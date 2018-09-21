<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/24
 * Time: 14:11
 */

namespace Track\Database\Concerns;

use Exception;
use Track\Support\Str;

trait DetectsLostConnections
{
    /**
     * 确定给定的异常是否由断开连接造成的
     *
     * @param Exception $exception
     *
     * @return bool
     */
    protected function causedByLostConnection( Exception $exception )
    {
        $message = $exception->getMessage();

        return Str::contains( $message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
        ] );
    }
}