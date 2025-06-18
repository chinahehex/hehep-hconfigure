# hehep-hconfigure

## 介绍
> hehep-hconfigure 是一个PHP配置基础组件，支持缓存功能  
> 默认支持php,json,ini,xml,yaml格式  

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
$config = [
    'onCache'=>true,// 是否开启缓存
    'cacheFile'=>'',// 缓存文件路径,
    'configFiles'=>[],// 默认配置文件集合
    'alias'=>[],// 别名集合
    // 默认支持的配置解析器
    'parserExts'=>['php', 'json', 'ini', 'xml', 'yaml'],
];


```

## 配置对象
- 说明
> 类名:hehe\core\hconfigure\Configure  
> 作用:配置解析器,设置缓存功能,获取配置项  
> 解释器:默认支持php,json,ini,xml,yaml格式

- 示例代码
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 设置配置文件
$hconfig->addFiles('user.php','user.json');

// 获取配置项
$username = $hconfig->get('name');
$hasNameStatus = $hconfig->has('name');

// 获取所有配置
$allConfig = $hconfig->getConfig();

```

## 获取配置项
```php
use hehe\core\hconfigure\Configure;
// 创建配置对象
$hconfig = new Configure();
// 设置配置文件
$hconfig->addFiles('user.php','user.json');

// 直接获取对象属性
$hconfig->appName;

// 获取配置项name
$username = $hconfig->get('name');

// 获取配置项name,不存在返回默认值"admin"
$username = $hconfig->get('name','admin');

// 获取二级配置项,配置项为user.name,
$username = $hconfig->get('user.name');

// 判断配置项name是否存在
$hasNameStatus = $hconfig->has('name');

```

## 配置文件
> 配置文件名格式: xxxx.配置项键名1.配置项键名2  
> 比如:config.components.php,配置项键名为components  
> 比如:config.components.hroute.php,配置项键名为:components['hroute']

- 添加配置项文件
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 给配置项"user",指定user.php 配置文件
$hconfig->addFile('user.php','user');

$hconfig->addFiles(['user.php','user'],'user.json');

// 给配置项"user.admin",指定admin.php 配置文件
$hconfig->addFiles(['admin.php','user.admin'],'user.json');
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

- 注册解析器
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();
$hconfig->addParser('ini',IniParser::class);
$hconfig->addParser('ini',[IniParser::class,'parse']);

// 设置闭包解析器
$hconfig->addParser('ini',function($file){
    return parse_ini_string(file_get_contents($file), true);
});

$allConfig = $hconfig->addFiles('user.ini')->load()->getConfig();

```

## 别名集合
> 作用:主要用于替换配置项  
> 别名引用格式@别名@  
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure([
    'alias'=>[
        'appPath'=>'/www/apps/admin',
        'appConfig'=>'@appPath@/config'
    ],
    
    'file'=>'@appPath@/config.php'
]);

// 设置appPath别名
$hconfig->setAlias('appPath','/www/apps/admin');

//获取别名
$hconfig->getAlias('@appPath');

$file = $hconfig->get('file');
// $file = /www/apps/admin/config.php


```

## 缓存配置

- 设置缓存文件路径
```php
use hehe\core\hconfigure\Configure;

// 创建配置对象
$hconfig = new Configure();

// 设置缓存文件路径
$hconfig->setCacheFile('cache/config.php');

$allConfig = $hconfig->addFiles('user.ini')->load()->getConfig();

```

- 添加缓存检测文件
```php
use hehe\core\hconfigure\Configure;
// 创建配置对象
$hconfig = new Configure();

// 设置缓存文件路径
$hconfig->setCacheFile('cache/config.php');

// 添加缓存文件,用于检测缓存是否有效
$hconfig->addCheckFile('adminuser.php');

$allConfig = $hconfig->addFiles('user.ini')->load()->getConfig();

```

## 扩展配置
> 基类:继承hehe\core\hconfigure\Configure

```php
<?php
namespace hehe\core\base;

use hehe\core\hconfigure\Configure;

class Config extends Configure
{
    // 项目名
    public $appName = '';

    // 定义其他属性
    
    // 重写以下方法
    
    /**
     * 自动导入配置文件
     */
    protected function autoloadFiles():void
    {
        parent::autoloadFiles();

        // 导入配置目录下的所有配置文件
        
    }
    
    protected function parseConfig():void
    {
        
    }
    
}


```




