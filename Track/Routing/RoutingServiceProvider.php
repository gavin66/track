<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 11:07
 */

namespace Track\Routing;


use Track\Foundation\ServiceProvider;
use Track\Http\ResponseFactory;
use Track\Http\ResponseFactoryContract;

class RoutingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerRouter();
        $this->registerResponseFactory();
    }

    /**
     * 路由器
     */
    protected function registerRouter()
    {
        $this->container->singleton( 'router', function ( $app ) {
            return new Router( $app );
        } );
    }

    /**
     * 响应
     */
    protected function registerResponseFactory()
    {
        $this->container->singleton( ResponseFactoryContract::class, function ( $app ) {
            return new ResponseFactory();
        } );
    }

    public function boot()
    {
        // 加载路由配置
        $this->container[ 'router' ]->loadRoutes( app()->basePath( 'app/routes.php' ) );
    }
}