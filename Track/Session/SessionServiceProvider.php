<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/25
 * Time: 18:42
 */

namespace Track\Session;


use Track\Foundation\ServiceProvider;
use Track\Middleware\StartSession;

class SessionServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->container->singleton( 'session', function ( $container ) {
            return new SessionManager( $container );
        } );

        $this->container->singleton( 'session.store', function ( $container ) {
            return $container->make( 'session' )->driver();
        } );

        $this->container->singleton( StartSession::class );
    }
}