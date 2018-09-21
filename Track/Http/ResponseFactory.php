<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/26
 * Time: 11:44
 */

namespace Track\Http;


class ResponseFactory implements ResponseFactoryContract
{
    public function __construct()
    {

    }

    public function make( $content = '', $status = 200, array $headers = [] )
    {
        return new Response( $content, $status, $headers );
    }

    public function json( $data = [], $status = 200, array $headers = [], $options = 0 )
    {
        return new JsonResponse( $data, $status, $headers, $options );
    }
}