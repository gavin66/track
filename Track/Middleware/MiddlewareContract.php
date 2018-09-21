<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/8
 * Time: 11:41
 */

namespace Track\Middleware;


use Track\Http\Request;

/**
 * 中间件必须实现的接口
 *
 * @package Track\Middleware
 */
interface MiddlewareContract
{
    /**
     * 中间件执行方法
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle( Request $request, \Closure $next );
}