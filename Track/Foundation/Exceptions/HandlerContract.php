<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/26
 * Time: 18:38
 */

namespace Track\Foundation\Exceptions;


use Track\Http\Request;

interface HandlerContract
{
    /**
     * 报告错误(记录日志)
     *
     * @param  \Exception $e
     *
     * @return void
     */
    public function report( \Exception $e );

    /**
     * 页面渲染错误
     *
     * @param  Request    $request
     * @param  \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render( Request $request, \Exception $e );
}