<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/9/21
 * Time: 10:44
 */

namespace App\Middleware;

use Track\Http\Request;
use Track\Middleware\MiddlewareContract;

class Hello implements MiddlewareContract
{
    public function handle( Request $request, \Closure $next )
    {
        echo 'HelloMiddleware echo<br>';

        return $next( $request );
    }

}