<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 11:18
 */

namespace Track\Routing;


interface RouterContract
{
    /**
     * get 请求路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function get( $uri, $action );

    /**
     * post 请求路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function post( $uri, $action );

    /**
     * put 请求路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function put( $uri, $action );

    /**
     * delete 请求路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function delete( $uri, $action );

    /**
     * 资源路由器
     *
     * @param string $name
     * @param string $controller
     * @param array  $options
     *
     * @return mixed
     */
    public function resource( $name, $controller, array $options = [] );

}