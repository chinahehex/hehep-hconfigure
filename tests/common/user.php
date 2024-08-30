<?php

return [
    'app_name' => 'hehe',
    'app_version' => '1.0.0',
    'app_env' => 'dev',
    'app_debug' => true,
    'app_timezone' => 'PRC',
    'app_locale' => 'zh-CN',
    'app_fallback_locale' => 'en',
    'app_key' => '123456',
    'app_cipher' => 'AES-256-CBC',
    'app_url' =>'http://localhost',
    'oss'=>[
        'bucket'=>'hehe',
        'endpoint'=>'http://localhost',
        'admin'=>[
            'policy'=>'admin',
            'bucket'=>'hehe',
            'endpoint'=>'http://localhost',
            'accessKeyId'=>'123456',
            'accessKeySecret'=>'123456',
        ],
    ],


];


?>
