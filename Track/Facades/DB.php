<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 10:52
 */

namespace Track\Facades;

use Track\Database\Connections\Connection;

/**
 * 数据库门面
 *
 * @method static select( $query, $bindings = [] )
 * @method static selectOne( $query, $bindings = [] )
 * @method static insert( $query, $bindings = [] )
 * @method static update( $query, $bindings = [] )
 * @method static delete( $query, $bindings = [] )
 * @method static statement( $query, $bindings = [] )
 * @method static reconnect()
 * @method static \PDO getPdo()
 * @method static setPdo( $pdo )
 * @method static setReconnector( callable $reconnector )
 * @method static bindValues( $statement, $bindings )
 * @method static getName()
 * @method static getDatabase()
 * @method static transaction( \Closure $callback, $attempts = 1 )
 * @method static selectDb( $database )
 * @method static Connection connection( $name = null )
 *
 * // 不规范,待删除
 * @method static insertByArray( $table, $data )
 * @method static updateByArray( $table, $id, $data )
 *
 * @package Track\Facades
 */
class DB extends Facade
{
    /**
     * 服务名
     *
     * @return string
     */
    protected static function getFacadeAccessName()
    {
        return 'db';
    }
}