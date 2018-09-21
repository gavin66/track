<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 15:37
 */

namespace Track\Http;

use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

class JsonResponse extends SymfonyJsonResponse
{
    /**
     * 生成 json 响应
     *
     * @param array $data
     * @param int   $status
     * @param array $headers
     * @param       $options
     */
    public function __construct( $data = null, $status = 200, $headers = [], $options = 0 )
    {
        $this->encodingOptions = $options;

        parent::__construct( $data, $status, $headers );
    }
}