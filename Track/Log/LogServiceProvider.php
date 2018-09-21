<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/28
 * Time: 11:05
 */

namespace Track\Log;

use Track\Foundation\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->container->singleton( 'log', function () {
            return $this->createLogger();
        } );
    }

    /**
     * 创建日志
     */
    protected function createLogger()
    {
        $logger = new Writer();
        if ( $this->container->bound( 'config' ) && $path = $this->container->make( 'config' )->get( 'app.log.dir' ) ) {
            $logger->setLogPath( $path );
        } else {
            $logger->setLogPath( $this->container->storagePath() . '/logs/' );
        }

        return $logger;
    }
}