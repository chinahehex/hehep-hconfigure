# hehep-hconfigure

## 介绍
- hehep-hconfigure 是一个PHP 配置基础组件，支持缓存功能

## 安装
- **gitee下载**:
```
git clone git@gitee.com:chinahehex/hehep-hconfigure.git
```

- **github下载**:
```
git clone git@github.com:chinahehex/hehep-hconfigure.git
```
- 命令安装：
```
composer require hehex/hehep-hconfigure
```


## 组件配置

```php
$eventConf = [
    // 默认支持的配置解析器
    'exts'=>[
        'php'=>'Php',
        'json'=>'Json',
        'ini'=>'Ini',
        'xml'=>'Xml',
        'yaml'=>'Yaml'
    ],
];


```

## 配置对象
- 说明
```
类名:hehe\core\hconfigure\Configure
作用:配置解析器,设置缓存功能,获取配置项
解释器:默认支持php,json,ini,xml,yaml格式
```

- 示例代码
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 设置配置文件
$hconfig->setFiles('user.php','user.json');

// 获取配置项
$username = $hconfig->get('name');
$hasNameStatus = $hconfig->has('name');

// 获取所有配置
$allConfig = $hconfig->getConfig();

```

## 配置文件
- 设置配置文件
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 设置配置文件
$hconfig->setFiles('user.php','user.json');
```

- 设置配置项文件
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 给配置项"user",指定user.php 配置文件
$hconfig->setFiles(['user.php','user'],'user.json');

// 给配置项"user.admin",指定admin.php 配置文件
$hconfig->setFiles(['admin.php','user.admin'],'user.json');
```

## 配置解析器
- 定义解析器
```php
class IniParser
{
    public static  function parse(string $file):array
    {
        return parse_ini_string(file_get_contents($file), true);
    }
}
```

- 使用解析器
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();
$hconfig->setParser('ini',IniParser::class);
$hconfig->setParser('ini',[IniParser::class,'parse']);

// 设置闭包解析器
$hconfig->setParser('ini',function($file){
    return parse_ini_string(file_get_contents($file), true);
});

$allConfig = $hconfig->setFiles('user.ini')->getConfig();

```

## 缓存配置

- 示例代码
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 设置缓存文件路径
$hconfig->setCacheFile('cache/config.php');

// 设置30 分钟缓存
$hconfig->setExpire(60 * 30);

$allConfig = $hconfig->setFiles('user.ini')->getConfig();

// 直接读取缓存文件
$cacheConfig = $hconfig->getCacheConfigFromFile();
```

## 清除配置
- 示例代码
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 设置缓存文件路径
$hconfig->setCacheFile('cache/config.php');

$allConfig = $hconfig->setFiles('user.ini')->getConfig();

// 清除配置数据,缓存,解析状态
$hconfig->cleanConfig();

// 重新加载配置
$allConfig = $hconfig->getConfig();
```






