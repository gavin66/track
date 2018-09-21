<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/11
 * Time: 17:20
 */

namespace Track\Foundation\Exceptions;

use ErrorException;
use ReflectionProperty;

class FatalErrorException extends ErrorException
{
    public function __construct( $message, $code, $severity, $filename, $lineno )
    {
        parent::__construct( $message, $code, $severity, $filename, $lineno );
    }

    protected function setTrace( $trace )
    {
        $traceReflector = new ReflectionProperty( 'Exception', 'trace' );
        $traceReflector->setAccessible( true );
        $traceReflector->setValue( $this, $trace );
    }
}