<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 11:36
 */

namespace Track\Routing;

use Track\Http\Request;
use Track\Routing\Exceptions\NotFoundHttpException;
use Track\Support\Arr;

/**
 * 路由集合,所有路由都会注册到这里
 *
 * @package Track\Routing
 */
class RouteCollection implements \Countable, \IteratorAggregate
{
    /**
     * 以动作(get,post等)作为 key 的路由数组
     *
     * @var array
     */
    protected $routes = [];

    /**
     * 所有的路由
     *
     * @var array
     */
    protected $allRoutes = [];

    /**
     * 添加一个路由
     *
     * @param  Route $route
     *
     * @return Route
     */
    public function add( Route $route )
    {
        $this->addToCollections( $route );

        return $route;
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param  Route $route
     *
     * @return void
     */
    protected function addToCollections( $route )
    {
        $domainAndUri = $route->getDomain() . $route->uri();

        foreach ( $route->methods() as $method ) {
            $this->routes[ $method ][ $domainAndUri ] = $route;
        }

        $this->allRoutes[ $method . $domainAndUri ] = $route;
    }

    /**
     * 找出第一个与请求匹配的路由
     * 如果未找到,抛出异常
     *
     * @param  Request $request
     *
     * @return Route
     *
     * @throws NotFoundHttpException
     */
    public function match( Request $request )
    {
        // 获取 method 所有路由
        $routes = $this->get( $request->getMethod() );

        // 获取当前请求的的路由
        $route = $this->matchAgainstRoutes( $routes, $request );

        if ( ! is_null( $route ) ) {
            return $route->bind($request);
        }

        throw new NotFoundHttpException;
    }

    /**
     * 返回当前请求解析出的路由
     *
     * @param  array   $routes
     * @param  Request $request
     *
     * @return Route|null
     */
    protected function matchAgainstRoutes( array $routes, $request )
    {
        return collect( $routes )->first( function ( $value ) use ( $request ) {
            return $value->matches( $request );
        } );
    }

    /**
     * 获取指定 method 的所有路由
     *
     * @param  string|null $method
     *
     * @return array
     */
    public function get( $method = null )
    {
        return is_null( $method ) ? array_values( $this->allRoutes ) : Arr::get( $this->routes, $method, [] );
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return array_values( $this->allRoutes );
    }

    public function getIterator()
    {
        return new \ArrayIterator( $this->getRoutes() );
    }

    public function count()
    {
        return count( $this->getRoutes() );
    }

}