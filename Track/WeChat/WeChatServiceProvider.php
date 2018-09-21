<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 11:07
 */

namespace Track\WeChat;


use Track\Foundation\ServiceProvider;
use Track\WeChat\OpenPlatform\OpenPlatform;

class WeChatServiceProvider extends ServiceProvider
{
    public function register()
    {
        $apps = [
            'open_platform' => OpenPlatform::class,
        ];

        foreach ( $apps as $name => $class ) {
            if ( empty( $config = config( "wechat.{$name}" ) ) )
                continue;

            $this->container[ 'router' ]->post( $config[ 'route' ][ 'uri' ], $config[ 'route' ][ 'action' ] );

            $this->container->singleton( "wechat.{$name}", function ( $application ) use ( $config, $class ) {
                return new $class( $application, $config );
            } );

            $this->container->alias( "wechat.{$name}", $class );
        }

    }

}