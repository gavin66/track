<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/4
 * Time: 17:49
 */

/**
 * 应用的开始时间
 */
define( 'TRACK_START', microtime( true ) );

/**
 * 自动加载
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * 获取应用
 */
$app = require_once __DIR__ . '/../app/boot.php';

// http 请求
$request = $app->instance( 'request', \Track\Http\Request::instance() );

// http 响应
$response = $app->handle( $request );

// 响应页面
$response->send();

