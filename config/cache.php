<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/13
 * Time: 11:05
 */

return [

    // 默认 redis
    'default' => 'redis',

    // 存储库
    'stores'  => [
        // 目前只有 redis 驱动可选 !!! 未来可能添加 memcached, file 等.
        // 默认 redis
        'redis'  => [
            'driver'   => env( 'CACHE_DRIVER', 'redis' ),
            'host'     => env( 'CACHE_HOST', '127.0.0.1' ),
            'port'     => env( 'CACHE_PORT', 6379 ),
            'password' => env( 'CACHE_PASSWORD', null ),
            'database' => 0,
        ],

        // 扩展其他文件配置
        'extend' => [

        ],

    ],

    // 缓存 key 的前缀
    'prefix'  => env( 'CACHE_PREFIX', '' ),

];