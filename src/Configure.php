<?php
namespace hehe\core\hconfigure;

/**
 * 配置类
 */
class Configure
{
    /**
     * 缓存文件
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $cacheFile = '';

    /**
     * 配置文件列表
     * 格式1:<文件1,文件2,<文件1,key>>
     * @var array
     */
    protected $files = [];

    /**
     * 缓存文件有效期
     *<B>说明：</B>
     *<pre>
     *  单位秒,0 表示永不用过期
     *</pre>
     * @var int
     */
    protected $expire = 0;

    /**
     * 文件后缀与解析类对应表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $exts = [
        'php'=>'Php',
        'json'=>'Json',
        'ini'=>'Ini',
        'xml'=>'Xml',
        'yaml'=>'Yaml'
    ];

    /**
     * 配置的所有数据
     * @var array
     */
    protected $data = [];

    /**
     * 是否已经解析过数据
     * @var bool
     */
    protected $parsed = false;

    public function __construct(array ...$files)
    {
        $this->files = $files;
    }

    public static function make(array ...$files)
    {
        return new static(...$files);
    }

    public function setExpire(int $expire):self
    {
        $this->expire = $expire;

        return $this;
    }

    public function setCacheFile(string $cacheFile):self
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }

    public function setParser(string $alias, $parser = ''):self
    {
        if ($parser === '') {
            $this->exts[$alias] = ucfirst($alias);
        } else {
            $this->exts[$alias] = $parser;
        }

        return $this;
    }

    public function setFiles(...$files):self
    {
        $this->files = array_merge($this->files,$files);

        return $this;
    }

    /**
     * 导入配置文件
     *<B>说明：</B>
     *<pre>
     *  最终返回数组
     *</pre>
     * @param string $file 文件路径
     * @return array
     */
    protected function loadFile(string $file):array
    {
        // 获取文件后缀
        $info = pathinfo($file);
        $ext = $info['extension'];

        if (!isset($this->exts[$ext])) {
            throw new \Exception(sprintf('ext %s not support', $ext));
        }

        $parseClass = $this->exts[$ext];

        $call = [];
        if (is_string($parseClass)) {
            if (strpos($parseClass,'\\') === false) {
                $parseClass = sprintf('%s\\parser\\%sParser', __NAMESPACE__,ucfirst($parseClass));
            }

            $call = [$parseClass,'parse'];
        } else {
            // 闭包函数或数组
            $call = $parseClass;
        }

        return call_user_func_array($call,[$file]);
    }

    /**
     * 获取配置文件内容
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $files 配置文件列表
     * @return array
     */
    protected function loadFiles(array $files):array
    {
        $data = [];
        foreach ($files as $file) {
            $key_name = '';
            if (is_array($file)) {
                list($filename,$key_name) = $file;
            } else {
                $filename = $file;
            }

            if (!empty($key_name)) {
                // 支持2级数组
                if (strpos($key_name,'.') !== false) {
                    list($key1,$key2) =  explode('.',$key_name);
                    if (isset($data[$key1][$key2])) {
                        $data[$key1][$key2] = array_merge($data[$key1][$key2],$this->loadFile($filename));
                    } else {
                        $data[$key1][$key2] = $this->loadFile($filename);;
                    }
                } else {
                    if (isset($data[$key_name])) {
                        $data[$key_name] = array_merge($data[$key_name],$this->loadFile($filename));
                    } else {
                        $data[$key_name] = $this->loadFile($filename);
                    }
                }
            } else {
                $data = array_merge($this->loadFile($filename),$data);
            }
        }

        return $data;
    }

    /**
     * 文件是否更新过
     *<B>说明：</B>
     *<pre>
     *  苦厄
     *</pre>
     * @param string $file 文件路径
     * @return boolean true 表示文件更新过,false 未更新
     */
    protected function hasFileUpdated(string $file):bool
    {
        $change_status = true;
        if (is_array($file)) {
            list($filename,$key) = $file;
        } else {
            $filename = $file;
        }

        if (!is_file($this->cacheFile)) {
            return $change_status;
        }

        // 如果缓存文件不存在
        if (is_file($filename)) {
            // 文件有更新
            if (filemtime($filename) < filemtime($this->cacheFile)) {
                $change_status = false;
                return $change_status;
            }

            // 判断缓存文件是否过期
            if ($this->expire > 0) {
                // 判断文件是否更新
                $nowtime = time();
                if (($nowtime + $this->expire) < filemtime($this->cacheFile)) {
                    $change_status = false;
                }
            }
        }

        return $change_status;
    }

    /**
     * 检测配置文件是否更新过
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return boolean true 表示更新过,false 未更新过
     */
    public function checkFileUpdated():bool
    {
        foreach ($this->files as $file) {
            $updateStatus = $this->hasFileUpdated($file);
            if ($updateStatus === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * 从配置文件中获取数据
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param bool $forceRawConfig 是否强制从原始配置文件中获取数据,反之从缓存文件中获取数据
     * @return array
     */
    public function getConfigFromFile(bool $forceRawConfig = false):array
    {
        if ($forceRawConfig) {
            $data = $this->getRawConfigFromFile();
        } else {
            if (!empty($this->cacheFile)) {
                $data = $this->getCacheConfigFromFile();
            } else {
                $data = $this->getRawConfigFromFile();
            }
        }

        return $data;
    }

    /**
     * 从缓存文件中获取配置信息
     * 如果配置文件有更新，则重新生成缓存文件
     * @return array
     */
    public function getCacheConfigFromFile():array
    {
        $fileUpdatedStatus = $this->checkFileUpdated();
        if ($fileUpdatedStatus === true) {
            $this->writeConfig($this->loadFiles($this->files));
        }

        return require($this->cacheFile);
    }

    public function getRawConfigFromFile():array
    {
        return $this->loadFiles($this->files);
    }

    public function parseConfig():self
    {
        if ($this->parsed) {
            return $this;
        }

        $this->data = $this->getConfigFromFile();
        $this->parsed = true;

        return $this;
    }

    /**
     * 获取配置信息
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return array
     */
    public function getConfig():array
    {
        $this->parseConfig();

        return $this->data;
    }

    public function cleanConfig():void
    {
        $this->data = [];
        $this->parsed = false;
        if (!empty($this->cacheFile) && file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    /**
     * 配置写入缓存文件
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $config 配置数据
     * @return void
     */
    public function writeConfig(array $config):void
    {
        $cache_file_dir = dirname($this->cacheFile);
        if (!is_dir($cache_file_dir)) {
            mkdir($cache_file_dir,0777,true);
        }

        file_put_contents($this->cacheFile,'<?php return ' . var_export($config,true) . ';');
    }

    public function get(string $name = null, $default = null)
    {
        $this->parseConfig();

        $nameArr = explode('.', $name);
        $config = $this->data;
        foreach ($nameArr as $key) {
            if (isset($config[$key])) {
                $config = $config[$key];
            } else {
                return $default;
            }
        }

        return $config;
    }

    public function has(string $name):bool
    {
        $this->parseConfig();

        if (strpos($name,'.') === false && !isset($this->data[$name])) {
            return false;
        }

        return is_null($this->get($name)) ? false : true;
    }
}
