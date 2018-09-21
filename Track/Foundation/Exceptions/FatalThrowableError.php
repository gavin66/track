<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/11
 * Time: 14:03
 */

namespace Track\Foundation\Exceptions;


class FatalThrowableError extends FatalErrorException
{
    public function __construct( \Throwable $throwable )
    {
        if ( $throwable instanceof \ParseError ) {
            $message  = 'Parse error: ' . $throwable->getMessage();
            $severity = E_PARSE;
        } elseif ( $throwable instanceof \TypeError ) {
            $message  = 'Type error: ' . $throwable->getMessage();
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $message  = $throwable->getMessage();
            $severity = E_ERROR;
        }

        \ErrorException::__construct(
            $message,
            $throwable->getCode(),
            $severity,
            $throwable->getFile(),
            $throwable->getLine()
        );

        $this->setTrace( $throwable->getTrace() );
    }
}