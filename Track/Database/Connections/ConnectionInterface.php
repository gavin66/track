<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/30
 * Time: 11:20
 */

namespace Track\Database\Connections;

use Closure;

interface ConnectionInterface
{

    /**
     * 执行 SQL 语句返回一条数据
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return mixed
     */
    public function selectOne( $query, $bindings = [] );

    /**
     *  执行一条 select 查询语句
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return array
     */
    public function select( $query, $bindings = [] );

    /**
     * 插入一条数据
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return bool
     */
    public function insert( $query, $bindings = [] );

    /**
     * 更新一条数据
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return int
     */
    public function update( $query, $bindings = [] );

    /**
     * 删除一条数据
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return int
     */
    public function delete( $query, $bindings = [] );

    /**
     * 执行 SQL 语句返回 bool 结果.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return bool
     */
    public function statement( $query, $bindings = [] );

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure $callback
     * @param  int      $attempts
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction( Closure $callback, $attempts = 1 );

    /**
     * 开始事务
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * 提交事务
     *
     * @return void
     */
    public function commit();

    /**
     * 回滚事务
     *
     * @return void
     */
    public function rollBack();

    /**
     *  获取事务的数量 savepoint
     *
     * @return int
     */
    public function transactionLevel();

}