<?php
namespace hehe\core\hconfigure;

/**
 * 配置解析
 */
class ConfigParser
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
     * 缓存文件列表
     * 用于检测文件是否更新
     * @var array
     */
    protected $cacheFiles = [];

    /**
     * 配置文件列表
     * 格式1:<文件1,文件2,<文件1,key>>
     * @var array
     */
    protected $files = [];
    
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


    public function __construct(array ...$files)
    {
        $this->files = $files;
        $this->cacheFiles = $files;
    }

    public function setCacheFile(string $cacheFile):self
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }

    public function addParser(string $alias, $parser = ''):self
    {
        if ($parser === '') {
            $this->exts[$alias] = ucfirst($alias);
        } else {
            $this->exts[$alias] = $parser;
        }

        return $this;
    }

    public function setParsers(array $parsers):self
    {
        $this->exts = array_merge($this->exts,$parsers);

        return $this;
    }

    public function addFile(...$files):self
    {
        $cackeFiles = [];
        foreach ($files as $file) {
            if (is_array($file)) {
                list($filepath,$key) = $file;
            } else {
                $filepath = $file;
            }

            $this->files[$filepath] = $file;
            $cackeFiles[] = $filepath;
        }

        $this->addCheckFile(...$cackeFiles);

        return $this;
    }

    public function addCheckFile(...$cacheFiles):self
    {
        foreach ($cacheFiles as $file) {
            $this->cacheFiles[$file] = $file;
        }

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
    public function parseFile(string $file):array
    {
        // 获取文件后缀
        $ext = pathinfo($file,PATHINFO_EXTENSION);
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
    public function loadFiles(...$files):array
    {
        $data = [];
        foreach ($files as $file) {
            $key = '';
            if (is_array($file)) {
                list($filename,$key) = $file;
            } else {
                $filename = $file;
            }

            if (!empty($key)) {
                // 支持2级数组
                if (strpos($key,'.') !== false) {
                    list($key1,$key2) =  explode('.',$key);
                    if (isset($data[$key1][$key2])) {
                        $data[$key1][$key2] = array_merge($data[$key1][$key2],$this->parseFile($filename));
                    } else {
                        $data[$key1][$key2] = $this->parseFile($filename);
                    }
                } else {
                    if (isset($data[$key])) {
                        $data[$key] = array_merge($data[$key],$this->parseFile($filename));
                    } else {
                        $data[$key] = $this->parseFile($filename);
                    }
                }
            } else {
                $data = array_merge($this->parseFile($filename),$data);
            }
        }

        return $data;
    }

    /**
     * 指定文件是否需要刷新
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $file 文件路径
     * @return boolean true 表示刷新,false 无需刷新
     */
    protected function fileIsFresh(string $file):bool
    {
        // 文件有更新
        if (filemtime($file) < filemtime($this->cacheFile)) {
            return false;
        }

        return true;
    }

    /**
     * 配置是否更新
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return boolean true 表示更新过,false 未更新过
     */
    public function isFresh():bool
    {
        if ($this->cacheFile === '' || !file_exists($this->cacheFile)) {
            return true;
        }

        foreach ($this->cacheFiles as $file) {
            if (is_array($file)) {
                list($filename,$key) = $file;
            } else {
                $filename = $file;
            }

            $freshStatus = $this->fileIsFresh($filename);
            if ($freshStatus === true) {
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
    public function getConfig(bool $forceRawConfig = false):array
    {
        if ($forceRawConfig) {
            $data = $this->getConfigFromFile();
        } else {
            if ($this->isFresh() === true) {
                $data = $this->getConfigFromFile();
                $this->writeConfig($data);
            } else {
                $data = $this->getConfigFromCache();
            }
        }

        return $data;
    }

    /**
     * 从缓存文件中获取配置信息
     * 如果配置文件有更新，则重新生成缓存文件
     * @return array
     */
    public function getConfigFromCache():array
    {
//        if (!file_exists($this->cacheFile)) {
//            $this->writeConfig($this->loadFiles(...array_values($this->files)));
//        }

        return require($this->cacheFile);
    }

    public function getConfigFromFile():array
    {
        return $this->loadFiles(...array_values($this->files));
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
        if ($this->cacheFile === '') {
            return;
        }

        $cache_file_dir = dirname($this->cacheFile);
        if (!is_dir($cache_file_dir)) {
            mkdir($cache_file_dir,0777,true);
        }

        file_put_contents($this->cacheFile,'<?php return ' . var_export($config,true) . ';');
    }

}
