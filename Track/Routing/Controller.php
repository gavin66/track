<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/9
 * Time: 16:46
 */

namespace Track\Routing;


class Controller
{
    /**
     * 控制器中间件
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * 注册控制器中间件
     *
     * @param  array|string|\Closure $middleware
     * @param  array                 $options
     *
     * @return \Track\Routing\ControllerMiddlewareOptions
     */
    public function middleware( $middleware, array $options = [] )
    {
        foreach ( (array)$middleware as $m ) {
            $this->middleware[] = [
                'middleware' => $m,
                'options'    => &$options,
            ];
        }

        return new ControllerMiddlewareOptions( $options );
    }

    /**
     * 获取控制器中间件
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}