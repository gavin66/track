<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/26
 * Time: 18:22
 */

namespace App\Exceptions;

use Track\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];
}