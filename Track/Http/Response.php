<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/19
 * Time: 18:02
 */

namespace Track\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    public function __construct( $content = '', $status = 200, array $headers = [] )
    {
        parent::__construct( $content, $status, $headers );
    }
}