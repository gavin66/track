<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/13
 * Time: 14:03
 */
return [

    // session 驱动
    'driver'          => env( 'SESSION_DRIVER', 'redis' ),
    // 指定 cache 中的 store
    'store'           => env( 'SESSION_STORE', 'redis' ),
    // 当使用 database 为驱动时,session 存储的表名
    'table'           => 'sessions',
    // 生存时间(分钟)
    'lifetime'        => env( 'SESSION_LIFETIME', 120 ),
    // 浏览器关闭,session 失效
    'expire_on_close' => false,
    // 触发 session 回收的概率
    // redis 为驱动时,不需要设置,因为 redis 可设置生存时间
    'lottery'         => [ 2, 100 ],
    // cookie 名称, 用来获取 session ID
    'cookie'          => env( 'SESSION_COOKIE', 'track_session' ),
    // cookie 路径
    'path'            => '/',
    // cookie 域
    'domain'          => env( 'SESSION_DOMAIN', null ),
    // 只有 https 连接才发送 cookie
    'secure'          => env( 'SESSION_SECURE_COOKIE', false ),
    // 禁止 js 访问 cookie
    'http_only'       => true,

    // Supported: "lax", "strict"
    'same_site'       => null,

];