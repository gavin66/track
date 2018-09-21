<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/1
 * Time: 17:20
 */

/**
 * 初始化应用
 */
$app = new \Track\Application( realpath( __DIR__ . '/../' ) );

// 绑定异常处理单例
$app->singleton( \Track\Foundation\Exceptions\HandlerContract::class, App\Exceptions\Handler::class );

// todo 加载中间件

return $app;