<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/5
 * Time: 11:04
 */

namespace Track\Http\Exceptions;


interface HttpExceptionInterface
{
    /**
     * http 状态码
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * 响应的头信息
     *
     * @return array
     */
    public function getHeaders();
}