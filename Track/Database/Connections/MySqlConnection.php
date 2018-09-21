<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/30
 * Time: 11:44
 */

namespace Track\Database\Connections;

use PDO;

class MySqlConnection extends Connection
{
    /**
     * 给语句绑定 value
     *
     * @param \PDOStatement $statement
     * @param array         $bindings
     */
    public function bindValues( $statement, $bindings )
    {
        foreach ( $bindings as $key => $value ) {
            $statement->bindValue(
                is_string( $key ) ? $key : $key + 1, $value,
                is_int( $value ) || is_float( $value ) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * todo 暂且这样的写法,仅仅服务当前项目
     *
     * @param $table
     * @param $data
     *
     * @return bool|string
     * @throws \Exception
     */
    public function insertByArray( $table, $data )
    {
        if ( ! is_array( $data ) || count( $data ) == 0 ) {
            return false;
        }

        $fields = array_keys( $data );

        array_walk( $fields, function ( &$item ) {
            $item = '`' . trim( $item ) . '`';
        } );

        $field = implode( ',', $fields );

        $query = 'INSERT INTO ' . $table . ' (' . $field . ') VALUES (' . str_repeat( '?,', count( $fields ) - 1 ) . '?' . ')';

        return $this->insert( $query, array_values( $data ) ) ? $this->getPdo()->lastInsertId() : false;
    }

    /**
     * todo 暂且这样的写法,仅仅服务当前项目
     *
     * @param $table
     * @param $id
     * @param $data
     *
     * @return bool|int
     */
    public function updateByArray( $table, $id, $data )
    {
        if ( ! is_array( $data ) || count( $data ) == 0 ) {
            return false;
        }

        $columns = rtrim( array_reduce( array_keys( $data ), function ( $carry, $key ) {
            return $carry . "`$key`=?,";
        }, '' ), ',' );

        $query = "UPDATE $table SET $columns WHERE id = '$id'";

        return $this->update( $query, array_values( $data ) );
    }

    /**
     * 切换当前连接的数据库
     *
     * @param $database
     *
     * @return void
     * @throws \Exception
     */
    public function selectDb( $database )
    {
        $this->run( '', [], function () use ( $database ) {
            $this->getPdo()->exec( "use `$database`" );
            $this->database = $database;
        } );
    }

}