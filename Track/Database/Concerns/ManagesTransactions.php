<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/23
 * Time: 18:15
 */

namespace Track\Database\Concerns;

use Closure;
use Exception;
use Throwable;

trait ManagesTransactions
{
    /**
     * 事务闭包,在事务中执行语句
     *
     * @param Closure $callback
     * @param int     $attempts
     *
     * @throws Exception|Throwable
     */
    public function transaction( Closure $callback, $attempts = 1 )
    {
        for ( $currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++ ) {
            $this->beginTransaction();

            try {
                $callback( $this );

                $this->commit();
            } catch ( Exception $exception ) {
                if ( $this->causedByDeadlock( $exception ) && $this->transactions > 1 ) {
                    $this->transactions--;
                    throw $exception;
                }

                $this->rollback();

                if ( $this->causedByDeadlock( $exception ) && $currentAttempt < $attempts ) {
                    return;
                }

                throw $exception;
            } catch ( Throwable $throwable ) {
                $this->rollback();

                throw $throwable;
            }
        }
    }

    /**
     * 开始事务
     *
     * @throws Exception
     */
    public function beginTransaction()
    {
        $this->createTransaction();

        // 事务数加1
        $this->transactions++;
    }

    /**
     * 创建事务
     *
     * @throws Exception
     */
    public function createTransaction()
    {
        if ( $this->transactions == 0 ) {
            try {
                $this->getPdo()->beginTransaction();
            } catch ( Exception $exception ) {
                if ( $this->causedByLostConnection( $exception ) ) {
                    $this->reconnect();

                    $this->pdo->beginTransaction();
                } else {
                    throw $exception;
                }
            }
        } elseif ( $this->transactions >= 1 ) {
            // todo 驱动不同,savepoints支持程度 不同, 目前只有 mysql  所以支持
            $this->getPdo()->exec( 'SAVEPOINT trans' . ( $this->transactions + 1 ) );
        }
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        // 事务数只有一个时才提交 有多 savepoint
        if ( $this->transactions == 1 ) {
            $this->getPdo()->commit();
        }

        $this->transactions = max( 0, $this->transactions - 1 );
    }

    /**
     * 事务回滚操作
     *
     * @param null $toLevel
     */
    public function rollback( $toLevel = null )
    {
        $toLevel = is_null( $toLevel ) ? $this->transactions - 1 : $toLevel;

        if ( $toLevel < 0 || $toLevel > $this->transactions ) {
            return;
        }

        if ( $toLevel == 0 ) {
            $this->getPdo()->rollBack();
        } elseif ( 1 == 1 ) {
            // todo 驱动不同,savepoints支持程度 不同, 目前只有 mysql  所以支持
            $this->getPdo()->exec( 'ROLLBACK TO SAVEPOINT ' . 'trans' . ( $toLevel + 1 ) );
        }

        $this->transactions = $toLevel;
    }

    /**
     * 获取事务数 savepoint
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }

}