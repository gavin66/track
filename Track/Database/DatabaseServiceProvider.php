<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/29
 * Time: 18:02
 */

namespace Track\Database;


use Track\Foundation\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerConnectionServices();
    }

    protected function registerConnectionServices()
    {
        $this->container->singleton( 'db.factory', function ( $container ) {
            return new ConnectionFactory( $container );
        } );

        $this->container->singleton( 'db', function ( $container ) {
            return new DatabaseManager( $container, $container[ 'db.factory' ] );
        } );

        $this->container->bind( 'db.connection', function ( $container ) {
            return $container[ 'db' ]->connection();
        } );
    }
}