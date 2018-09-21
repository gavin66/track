<?php

return [
    // 初始化默认时区
    'timezone'  => 'PRC',

    // 加密密钥
    'key'       => env( 'APP_KEY' ),

    // 加密算法
    'cipher'    => 'AES-256-CBC',

    // 错误信息是否显示
    'debug'     => env( 'APP_DEBUG', false ),

    // 日志
    'log'       => [
        'dir' => env( 'LOG_PATH', '' ),
    ],

    // 服务提供者,创建应用就会注册
    'providers' => [
        // 日志
        \Track\Log\LogServiceProvider::class,
        // 数据库
        \Track\Database\DatabaseServiceProvider::class,
        // 缓存
        \Track\Cache\CacheServiceProvider::class,
        // 路由
        \Track\Routing\RoutingServiceProvider::class,
        // session
        \Track\Session\SessionServiceProvider::class,
        // 加密
        \Track\Encryption\EncryptionServiceProvider::class,
        // 微信
        \Track\WeChat\WeChatServiceProvider::class,
    ],
];
