<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Track\Foundation\Exceptions;

/**
 * FlattenException wraps a PHP Exception to be able to serialize it.
 *
 * Basically, this class removes all objects from the trace.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FlattenException
{
    private $message;
    private $code;
    private $previous;
    private $trace;
    private $class;
    private $statusCode;
    private $headers;
    private $file;
    private $line;

    public static function create( \Exception $exception, $statusCode = null, array $headers = [] )
    {
        $e = new static();
        $e->setMessage( $exception->getMessage() );
        $e->setCode( $exception->getCode() );

        if ( null === $statusCode ) {
            $statusCode = 500;
        }

        $e->setStatusCode( $statusCode );
        $e->setHeaders( $headers );
        $e->setTraceFromException( $exception );
        $e->setClass( get_class( $exception ) );
        $e->setFile( $exception->getFile() );
        $e->setLine( $exception->getLine() );

        $previous = $exception->getPrevious();

        if ( $previous instanceof \Exception ) {
            $e->setPrevious( static::create( $previous ) );
        } elseif ( $previous instanceof \Throwable ) {
            $e->setPrevious( static::create( new FatalThrowableError( $previous ) ) );
        }

        return $e;
    }

    public function toArray()
    {
        $exceptions = [];
        foreach ( array_merge( [ $this ], $this->getAllPrevious() ) as $exception ) {
            $exceptions[] = [
                'message' => $exception->getMessage(),
                'class'   => $exception->getClass(),
                'trace'   => $exception->getTrace(),
            ];
        }

        return $exceptions;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode( $code )
    {
        $this->statusCode = $code;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders( array $headers )
    {
        $this->headers = $headers;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass( $class )
    {
        $this->class = $class;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile( $file )
    {
        $this->file = $file;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setLine( $line )
    {
        $this->line = $line;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage( $message )
    {
        $this->message = $message;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode( $code )
    {
        $this->code = $code;
    }

    public function getPrevious()
    {
        return $this->previous;
    }

    public function setPrevious( FlattenException $previous )
    {
        $this->previous = $previous;
    }

    public function getAllPrevious()
    {
        $exceptions = [];
        $e          = $this;
        while ( $e = $e->getPrevious() ) {
            $exceptions[] = $e;
        }

        return $exceptions;
    }

    public function getTrace()
    {
        return $this->trace;
    }

    public function setTraceFromException( \Exception $exception )
    {
        $this->setTrace( $exception->getTrace(), $exception->getFile(), $exception->getLine() );
    }

    public function setTrace( $trace, $file, $line )
    {
        $this->trace   = [];
        $this->trace[] = [
            'namespace'   => '',
            'short_class' => '',
            'class'       => '',
            'type'        => '',
            'function'    => '',
            'file'        => $file,
            'line'        => $line,
            'args'        => [],
        ];
        foreach ( $trace as $entry ) {
            $class     = '';
            $namespace = '';
            if ( isset( $entry[ 'class' ] ) ) {
                $parts     = explode( '\\', $entry[ 'class' ] );
                $class     = array_pop( $parts );
                $namespace = implode( '\\', $parts );
            }

            $this->trace[] = [
                'namespace'   => $namespace,
                'short_class' => $class,
                'class'       => isset( $entry[ 'class' ] ) ? $entry[ 'class' ] : '',
                'type'        => isset( $entry[ 'type' ] ) ? $entry[ 'type' ] : '',
                'function'    => isset( $entry[ 'function' ] ) ? $entry[ 'function' ] : null,
                'file'        => isset( $entry[ 'file' ] ) ? $entry[ 'file' ] : null,
                'line'        => isset( $entry[ 'line' ] ) ? $entry[ 'line' ] : null,
                'args'        => isset( $entry[ 'args' ] ) ? $this->flattenArgs( $entry[ 'args' ] ) : [],
            ];
        }
    }

    private function flattenArgs( $args, $level = 0, &$count = 0 )
    {
        $result = [];
        foreach ( $args as $key => $value ) {
            if ( ++$count > 1e4 ) {
                return [ 'array', '*SKIPPED over 10000 entries*' ];
            }
            if ( $value instanceof \__PHP_Incomplete_Class ) {
                // is_object() returns false on PHP<=7.1
                $result[ $key ] = [ 'incomplete-object', $this->getClassNameFromIncomplete( $value ) ];
            } elseif ( is_object( $value ) ) {
                $result[ $key ] = [ 'object', get_class( $value ) ];
            } elseif ( is_array( $value ) ) {
                if ( $level > 10 ) {
                    $result[ $key ] = [ 'array', '*DEEP NESTED ARRAY*' ];
                } else {
                    $result[ $key ] = [ 'array', $this->flattenArgs( $value, $level + 1, $count ) ];
                }
            } elseif ( null === $value ) {
                $result[ $key ] = [ 'null', null ];
            } elseif ( is_bool( $value ) ) {
                $result[ $key ] = [ 'boolean', $value ];
            } elseif ( is_int( $value ) ) {
                $result[ $key ] = [ 'integer', $value ];
            } elseif ( is_float( $value ) ) {
                $result[ $key ] = [ 'float', $value ];
            } elseif ( is_resource( $value ) ) {
                $result[ $key ] = [ 'resource', get_resource_type( $value ) ];
            } else {
                $result[ $key ] = [ 'string', (string)$value ];
            }
        }

        return $result;
    }

    private function getClassNameFromIncomplete( \__PHP_Incomplete_Class $value )
    {
        $array = new \ArrayObject( $value );

        return $array[ '__PHP_Incomplete_Class_Name' ];
    }
}
