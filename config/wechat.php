<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/14
 * Time: 15:52
 */
return [
    // 开放平台
    'open_platform' => [
        'app_id'  => env( 'WECHAT_OPEN_PLATFORM_APPID', '' ),
        'secret'  => env( 'WECHAT_OPEN_PLATFORM_SECRET', '' ),
        'token'   => env( 'WECHAT_OPEN_PLATFORM_TOKEN', '' ),
        'aes_key' => env( 'WECHAT_OPEN_PLATFORM_AES_KEY', '' ),
        'route'   => [
            'uri'    => 'open-platform',
            'action' => \Track\WeChat\Controllers\OpenPlatformController::class,
        ],
    ],

];