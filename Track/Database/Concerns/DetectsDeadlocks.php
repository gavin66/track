<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/24
 * Time: 15:56
 */

namespace Track\Database\Concerns;

use Exception;
use Track\Support\Str;

trait DetectsDeadlocks
{
    /**
     * 确定给定的异常是否由死锁造成的
     *
     * @param Exception $e
     *
     * @return bool
     */
    protected function causedByDeadlock( Exception $e )
    {
        $message = $e->getMessage();

        return Str::contains( $message, [
            'Deadlock found when trying to get lock',
            'deadlock detected',
            'The database file is locked',
            'database is locked',
            'database table is locked',
            'A table in the database is locked',
            'has been chosen as the deadlock victim',
            'Lock wait timeout exceeded; try restarting transaction',
            'WSREP detected deadlock/conflict and aborted the transaction. Try restarting the transaction',
        ] );
    }
}