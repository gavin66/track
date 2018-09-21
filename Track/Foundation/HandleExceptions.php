<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/28
 * Time: 15:14
 */

namespace Track\Foundation;


use Track\Application;
use Exception;
use ErrorException;
use Track\Foundation\Exceptions\FatalThrowableError;
use Track\Foundation\Exceptions\Handler;
use Track\Foundation\Exceptions\FatalErrorException;
use Track\Foundation\Exceptions\HandlerContract;

class HandleExceptions
{
    /**
     * @var Application
     */
    protected $container;

    /**
     * 异常&错误 处理
     *
     * @param Application $app
     */
    public function bootstrap( Application $app )
    {
        $this->container = $app;

        // 报告所有错误
        error_reporting( -1 );

        // 错误不输出页面
        ini_set( 'display_errors', 'Off' );

        set_error_handler( [ $this, 'handleError' ] );

        set_exception_handler( [ $this, 'handleException' ] );

        register_shutdown_function( [ $this, 'handleShutdown' ] );

    }

    /**
     * 把 errors 转换成 ErrorException 实例
     *
     * @param        $level
     * @param        $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @throws ErrorException
     */
    public function handleError( $level, $message, $file = '', $line = 0, $context = [] )
    {
        if ( error_reporting() & $level ) {
            throw new ErrorException( $message, 0, $level, $file, $line );
        }
    }

    /**
     * 处理未捕获到的异常
     *
     * 自 PHP 7 以来，大多数错误抛出 Error 异常，也能被捕获。 Error 和 Exception 都实现了 Throwable 接口。
     * http://php.net/manual/zh/function.set-exception-handler.php
     *
     * @param $exception
     */
    public function handleException( $exception )
    {
        if ( ! $exception instanceof Exception ) {
            $exception = new FatalThrowableError( $exception );
        }

        try {
            $this->getExceptionHandler()->report( $exception );
        } catch ( Exception $exception ) {

        }

        $this->getExceptionHandler()->render( $this->container[ 'request' ], $exception )->send();
    }

    /**
     * PHP 脚本结束时调用
     */
    public function handleShutdown()
    {
        if ( ! is_null( $error = error_get_last() ) && in_array( $error[ 'type' ], [ E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE ] ) ) {
            $this->handleException( new FatalErrorException( $error[ 'message' ], $error[ 'type' ], 0, $error[ 'file' ], $error[ 'line' ] ) );
        }
    }

    /**
     * 获取错误处理器
     *
     * @return Handler
     */
    public function getExceptionHandler()
    {
        return call_user_func( [ $this->container, 'make' ], HandlerContract::class );
    }
}