<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/13
 * Time: 12:11
 */

return [
    // 默认使用 mysql
    'default'     => 'mysql',

    // 连接信息
    // 目前只有 mysql 驱动可选 !!! 未来可能添加 pgsql, mongodb 等.
    'connections' => [

        'mysql' => [
            'driver'    => env( 'DB_DRIVER', 'mysql' ),
            'host'      => env( 'DB_HOST', '127.0.0.1' ),
            'port'      => env( 'DB_PORT', '3306' ),
            'database'  => env( 'DB_DATABASE', '' ),
            'username'  => env( 'DB_USERNAME', '' ),
            'password'  => env( 'DB_PASSWORD', '' ),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],

    ],
];