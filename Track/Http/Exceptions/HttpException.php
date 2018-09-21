<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/5
 * Time: 11:04
 */

namespace Track\Http\Exceptions;


class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    private $statusCode;
    private $headers;

    public function __construct( $statusCode, $message = null, \Exception $previous = null, array $headers = [], $code = 0 )
    {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;

        parent::__construct( $message, $code, $previous );
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 设置响应头
     *
     * @param array $headers Response headers
     */
    public function setHeaders( array $headers )
    {
        $this->headers = $headers;
    }
}