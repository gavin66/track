<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 10:52
 */

namespace Track\Facades;
use Track\Routing\Route as RouteReal;
/**
 * 路由
 *
 * @method static RouteReal get( string $uri, \Closure | array | string | null $action = null )
 * @method static RouteReal post( string $uri, \Closure | array | string | null $action = null )
 * @method static RouteReal put( string $uri, \Closure | array | string | null $action = null )
 * @method static RouteReal delete( string $uri, \Closure | array | string | null $action = null )
 * @method static RouteReal resource( string $name, string $controller, array $options = [] )
 *
 * @package Track\Facades
 */
class Route extends Facade
{
    /**
     * 服务名
     *
     * @return string
     */
    protected static function getFacadeAccessName()
    {
        return 'router';
    }
}