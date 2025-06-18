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
    public $parserExts = ['php', 'json', 'ini', 'xml', 'yaml'];

    // 默认读取配置文件
    public $configFiles = [];

    /**
     * 命名空间别名
     *<B>说明：</B>
     *<pre>
     *  [
     *      'extend'=>'@root/extend',
     *      'common'=>'@apps/common',
     *      'Doctrine'=>'@vendor/Doctrine',
     *      'MongoDB'=>'@vendor/MongoDB',
     * ]
     *</pre>
     * @var array
     */
    public $alias = [];


    // 所有配置
    protected $_params = [];

    /**
     * 配置解析对象
     * @var ConfigParser
     */
    protected $_configParser = null;

    // 是否已经加载过配置
    protected $_loaded = false;

    protected $_files = [];
    protected $_allfile = [];
    protected $_checkFiles = [];


    /**
     * 构造方法
     * @param array $configs 应用配置
     */
    public function __construct($configs = [])
    {
        $checkFiles = [__FILE__,(new \ReflectionClass($this))->getFileName()];
        if (!empty($configs)) {
            if (is_string($configs) && file_exists($configs)) {
                $checkFiles[] = $configs;
                $configs = require $configs;
            }

            if (is_array($configs)) {
                foreach ($configs as $name=>$val) {
                    $this->{$name} = $val;
                }
            }
        }

        $this->addCheckFile(...$checkFiles);
    }

    protected function getConfigParser():ConfigParser
    {
        if (!is_null($this->_configParser)) {
            return $this->_configParser;
        }

        $this->_configParser = new ConfigParser();
        $this->_configParser->setParsers($this->parserExts)
        ->setCacheFile($this->getCacheFile());

        return $this->_configParser;
    }

    public function addParser(string $alias, $parser = ''):self
    {
        $this->getConfigParser()->addParser($alias,$parser);

        return $this;
    }

    public function addCheckFile(...$files):self
    {
        $this->_checkFiles = array_merge($this->_checkFiles,$files);

        return $this;
    }

    public function setCacheFile(string $cacheFile):self
    {
        $this->onCache = true;
        $this->cacheFile = $cacheFile;
        $this->getConfigParser()->setCacheFile($cacheFile);

        return $this;
    }

    public function offsetExists($offset):bool
    {
        if (property_exists($this,$offset)) {
            return true;
        } else {
            return false;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value):void
    {
        $this->{$offset} = $value;
        $this->_params[$offset] = $value;

        return ;
    }

    public function offsetUnset($offset):void
    {
        throw new \Exception('not support unset');
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
        if ($this->_loaded === true) {
            return $this;
        }

        // 自动导入配置文件
        $this->autoloadFiles();

        $configParser = $this->getConfigParser();
        $configParser->addFiles($this->_files)->addCheckFile(...$this->_checkFiles);
        if ($configParser->isFresh()) {
            $this->loadConfigFromFile();
        } else {
            $this->loadConfigFromCache();
        }

        $this->_loaded = true;

        return $this;
    }

    /**
     * 自动导入配置文件
     */
    protected function autoloadFiles():void
    {
        if (!empty($this->configFiles)) {
            $this->addFiles(...$this->configFiles);
        }
    }

    protected function loadConfigFromFile():void
    {
        $configParser = $this->getConfigParser();
        $this->_params = array_merge($this->getAllAttributes(),$configParser->getConfigFromFile());
        $this->configToAttribute();
        $this->parseConfig();
        $this->_params = $this->doFormatConfig(array_merge($this->_params,$this->getAllAttributes()));
        $this->configToAttribute();

        // 写入缓存
        if ($this->onCache) {
            $configParser->writeConfig([$this->_allfile,$this->toArray()]);
        }
    }

    protected function loadConfigFromCache():void
    {
        $configParser = $this->getConfigParser();
        list($this->_params,$files) = $configParser->getConfigFromCache();
        // 直接读取缓存文件
        if ($files == $this->_allfile) {
            $this->configToAttribute();
        } else {
            $this->_params = [];
            $this->loadConfigFromFile();
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
        return $this->_params;
    }

    public function getConfig():array
    {
        return $this->toArray();
    }

    public function addFile(string $file,string $key = ''):self
    {
        $this->_loaded = false;
        $this->_files[$file] = $key;
        $this->_allfile[] = $file;

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
            if (is_array($file)) {
                list($filepath,$key) = $file;
                $this->addFile($filepath,$key);
            } else {
                $this->addFile($file);
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

        $parserExts = $this->getConfigParser()->getExts();
        if ($match === '') {
            $match = '/^.*\.(' . implode('|', $parserExts) . ')$/';
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

    public function hasAlias(string $alias):bool
    {
        return isset($this->alias[$alias]);
    }

    public function getAlias($alias)
    {
        if (!is_string($alias)) {
            return $alias;
        }

        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }

        $alias = substr($alias,1);
        $pos = strpos($alias, '/');

        if ($pos === false) {
            $alias_name = $alias;
            return $this->alias[$alias_name];
        } else {
            $alias_name = substr($alias, 0, $pos);
            $path = substr($alias, $pos + 1);
            return $this->alias[$alias_name] . $path;
        }
    }

    public function setAlias(string $alias,$path):void
    {
        if (is_string($path)) {
            $this->alias[$alias] = $this->getAlias($path);
        } else {
            $this->alias[$alias] = $path;
        }
    }

    protected function doFormatConfig($config)
    {
        foreach ($config as $name=>$value) {
            if (is_array($value)) {
                $config[$name] = $this->doFormatConfig($value);
            } else {
                if (is_object($value)) {
                    continue;
                }

                if (preg_match('/@(\w+)@(.*)/', $value, $matches)) {
                    if (!isset($matches[2]) ||  $matches[2] === '') {
                        $config[$name] = $this->getAlias("@".$matches[1]);
                    } else {
                        $config[$name] = $this->getAlias("@".$matches[1]).$matches[2] ;
                    }
                } else {
                    $config[$name] = $value;
                }
            }
        }

        return $config;
    }

    // 配置启动入口
    protected function parseConfig()
    {

    }


}
