<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 16:35
 */

namespace Track\Routing\Matching;


use Track\Http\Request;
use Track\Routing\Route;

class UriValidator implements ValidatorInterface
{
    public function matches( Route $route, Request $request )
    {
        $path = $request->path() == '/' ? '/' : '/' . $request->path();

        // 没有实现路由参数等功能, 需正则
        return preg_match( $route->getCompiled()->getRegex(), rawurldecode( $path ) );
    }
}