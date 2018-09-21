<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/5
 * Time: 11:02
 */

namespace Track\Routing\Exceptions;


use Track\Http\Exceptions\HttpException;

class NotFoundHttpException extends HttpException
{
    public function __construct( $statusCode = null, $message = null, \Exception $previous = null, array $headers = [], $code = 0 )
    {
        parent::__construct( 404, $message, $previous, $headers, $code );
    }
}