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

class MethodValidator implements ValidatorInterface
{
    public function matches( Route $route, Request $request )
    {
        return in_array( $request->getMethod(), $route->methods() );
    }
}