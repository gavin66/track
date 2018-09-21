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

interface ValidatorInterface
{
    /**
     * 根据规则验证路由与请求是否匹配
     *
     * @param Route   $route
     * @param Request $request
     *
     * @return  bool
     */
    public function matches( Route $route, Request $request );
}