<?php
namespace hehe\core\hconfigure;

use ArrayAccess;

class Configure implements ArrayAccess
{
    // 缓存文件路径
    public $cacheFile = '';

    // 是否缓存配置,true 缓存配置
    public $onCache = true;

    // 解析配置文件后缀
    public $parserExts = [
        'php'=>'php',
        'json'=>'Json',
        'ini'=>'Ini',
        'xml'=>'Xml',
        'yaml'=>'Yaml'
    ];


    // 用户配置
    protected $_params = [];

    /**
     * 配置解析对象
     * @var ConfigParser
     */
    protected $_configParser = null;

    // 是否需要刷新缓存
    protected $_isFresh = false;

    // 是否已经加载过配置
    protected $_isLoad = false;

    protected $heheLoadFiles = [];
    protected $_heheLoadFiles = [];

    /**
     * 构造方法
     * @param array $configs 应用配置
     */
    public function __construct($configs = [])
    {
        if (!empty($configs)) {
            if (is_string($configs) && file_exists($configs)) {
                $this->addFile($configs);
            } else if (is_array($configs)) {
                foreach ($configs as $name=>$val) {
                    $this->{$name} = $val;
                }
            }
        }

        $configFilePath = (new \ReflectionClass($this))->getFileName();
        $this->addCheckFile($configFilePath,__FILE__);
    }

    protected function getConfigParser():ConfigParser
    {
        if (!is_null($this->_configParser)) {
            return $this->_configParser;
        }

        $this->_configParser = new ConfigParser();
        $this->_configParser->setParsers($this->parserExts);
        $this->setCacheFile($this->getCacheFile());

        return  $this->_configParser;
    }

    public function addParser(string $alias, $parser = ''):self
    {
        $this->getConfigParser()->addParser($alias,$parser);

        return $this;
    }

    public function addCheckFile(...$files):self
    {
        $this->getConfigParser()->addCheckFile(...$files);

        return $this;
    }

    public function setCacheFile(string $cacheFile):self
    {
        $this->getConfigParser()->setCacheFile($cacheFile);
        $this->onCache = true;
        $this->cacheFile = $cacheFile;

        return $this;
    }


    public function offsetExists($offset)
    {
        if (property_exists($this,$offset)) {
            return true;
        } else {
            return false;
        }
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
        $this->_params[$offset] = $value;

        return ;
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function __set($name,$value)
    {
        $this->_params[$name] = $value;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * 获取指定配置项
     *<B>说明：</B>
     *<pre>
     * 多级配置项支持. 方式,如auth.user
     *</pre>
     * @param string $key
     * @param string $defval 默认值
     * @return mixed
     */
    public function get(string $key,$defval = null)
    {
        $keys = explode('.',$key);
        $value = null;

        foreach ($keys as $key) {
            if (!is_null($value)) {
                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return $defval;
                }
            } else if (isset($this->_params[$key])) {
                $value = $this->_params[$key];
            } else {
                return $defval;
            }
        }

        return $value;
    }

    public function has(string $name):bool
    {
        if (strpos($name,'.') === false) {
            return isset($this->_params[$name]);
        } else {
            return is_null($this->get($name)) ? false : true;
        }
    }

    /**
     * 加载配置
     *<B>说明：</B>
     *<pre>
     * 从配置文件把数据加载到属性中
     *</pre>
     * @return self
     */
    public function load():self
    {
        $configParser = $this->getConfigParser();
        if ($configParser->isFresh()) {
            $this->loadFileConfig();
        } else {
            $this->loadCacheConfig();
        }

        $this->_isLoad = true;

        return $this;
    }

    /**
     * 验证缓存数据是否有效
     * @param array $params
     * @return bool
     */
    protected function validCacheParams(array $params):bool
    {
        // 验证数量是否一致
        if (count($params['heheLoadFiles']) !== count($this->_heheLoadFiles)) {
            return false;
        }

        // 验证新增的文件是否与缓存文件一致
        foreach ($this->_heheLoadFiles as $file=>$status) {
            if (!isset($params['heheLoadFiles'][$file])) {
                return false;
            }
        }

        return true;
    }

    protected function loadFileConfig():void
    {
        $configParser = $this->getConfigParser();
        $this->_params = array_merge($this->getAllAttributes(),$configParser->getConfigFromFile());
        $this->heheLoadFiles = $this->_heheLoadFiles;
        $this->configToAttribute();
        $this->parseConfig();
        $this->writeConfigCache();
    }

    protected function loadCacheConfig():void
    {
        $configParser = $this->getConfigParser();
        $params = $configParser->getConfigFromCache();
        // 直接读取缓存文件
        if ($this->validCacheParams($params)) {
            $this->heheLoadFiles = $this->_heheLoadFiles;
            $this->_params = $params;
            $this->configToAttribute();
        } else {
            $this->loadFileConfig();
        }
    }

    /**
     * 获取缓存配置文件
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return string
     */
    public function getCacheFile():string
    {
        return $this->cacheFile;
    }

    /**
     * 配置数据转为配置对象属性
     */
    protected function configToAttribute():void
    {
        foreach ((new \ReflectionClass($this))->getProperties() as $attribute) {
            if (substr($attribute->name,0,1) === '_') {
                continue;
            }

            $name = $attribute->name;
            if (isset($this->_params[$name])) {
                $this->{$name} = $this->_params[$name];
            }
        }
    }

    protected function getAllAttributes():array
    {
        $attrs = [];
        foreach ((new \ReflectionClass($this))->getProperties() as $attribute) {
            if (substr($attribute->name,0,1) === '_') {
                continue;
            }

            $name = $attribute->name;
            $attrs[$attribute->name] = $this->{$name};
        }

        return $attrs;
    }

    /**
     * 获取所有配置信息
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return array
     */
    public function toArray():array
    {
        return array_merge($this->_params,$this->getAllAttributes());
    }

    public function getConfig():array
    {
        return $this->toArray();
    }

    /**
     * 写入配置数据至缓存文件
     * @return void
     */
    protected function writeConfigCache():void
    {
        if ($this->onCache) {
            $this->getConfigParser()->writeConfig($this->toArray());
        }
    }

    public function addFile(string $file,string $key = ''):self
    {
        $filename = pathinfo($file,PATHINFO_FILENAME);
        if ($key === '' && strpos($filename,'.') !== false) {
            $filenames = explode('.',$filename);
            if (count($filenames) >=3) {
                $filenames = array_slice($filenames, -2);
                $key = implode('.',$filenames);
            } else {
                $key = $filenames[count($filenames) - 1];
            }
        }

        $this->getConfigParser()->addFile([$file,$key]);

        if (!isset($this->heheLoadFiles[$file])) {
            $this->_isFresh = true;
        }

        $this->_heheLoadFiles[$file] = true;

        return $this;
    }

    /**
     * 加载配置文件
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param array $files
     */
    public function addFiles(...$files):self
    {
        foreach ($files as $file) {
            $key = '';
            $filepath = '';
            if (is_array($file)) {
                list($filepath,$key) = $file;
            } else {
                $filepath = $file;
            }

            if (!empty($key)) {
                $this->addFile($filepath,$key);
            } else {
                $this->addFile($filepath);
            }
        }

        return $this;
    }

    /**
     * 加载目录下的配置文件
     * @param string $dir 目录地址
     * @param string $match 匹配规则
     * @return self
     */
    public function addDir(string $dir,string $match = ''):self
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        if ($match === '') {
            $match = '/^.*\.(' . implode('|', array_keys($this->parserExts)) . ')$/';
        }

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (preg_match($match, $file->getFilename(), $matches)) {
                $this->addFile($file->getRealPath());
            }
        }

        return $this;
    }

    /**
     * 解析配置文件,并返回配置数据
     * @param string $file
     * @return array
     */
    public function parseFile(string $file):array
    {
        return $this->getConfigParser()->parseFile($file);
    }


    // 配置启动入口
    protected function parseConfig()
    {

    }


}
