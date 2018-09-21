<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/26
 * Time: 11:42
 */

namespace Track\Http;


interface ResponseFactoryContract
{
    /**
     * 返回响应
     *
     * @param  string $content
     * @param  int    $status
     * @param  array  $headers
     *
     * @return Response
     */
    public function make( $content = '', $status = 200, array $headers = [] );

    /**
     * 返回 json 响应
     *
     * @param  string|array $data
     * @param  int          $status
     * @param  array        $headers
     * @param  int          $options
     *
     * @return JsonResponse
     */
    public function json( $data = [], $status = 200, array $headers = [], $options = 0 );
}