<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 13:56
 */

namespace Track\Cache;


use Track\Foundation\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->container->singleton( 'cache', function ( $container ) {
            return new CacheManager( $container );
        } );

        $this->container->singleton( 'cache.store', function ( $container ) {
            return $container[ 'cache' ]->driver();
        } );

    }

}