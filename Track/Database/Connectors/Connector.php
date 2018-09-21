<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/30
 * Time: 13:56
 */

namespace Track\Database\Connectors;


use Track\Database\Concerns\DetectsLostConnections;
use PDO;
use Exception;

class Connector
{
    use DetectsLostConnections;

    /**
     * PDO 默认连接属性
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * 连接数据库
     *
     * @param       $dsn
     * @param array $config
     * @param array $options
     *
     * @return PDO
     * @throws Exception
     */
    public function createConnection( $dsn, array $config, array $options )
    {
        list( $username, $password ) = [
            $config[ 'username' ] ? : null, $config[ 'password' ] ? : null
        ];

        try {
            return $this->createPdoConnection( $dsn, $username, $password, $options );
        } catch ( Exception $exception ) {
            return $this->tryAgainIfCausedByLostConnection( $exception, $dsn, $username, $password, $options );
        }

    }

    /**
     * 创建 PDO 连接
     *
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     *
     * @return PDO
     */
    protected function createPdoConnection( $dsn, $username, $password, $options )
    {
        return new PDO( $dsn, $username, $password, $options );
    }

    /**
     * 如果因为连接断开,那么就重试一次
     *
     * @param Exception $exception
     * @param           $dsn
     * @param           $username
     * @param           $password
     * @param           $options
     *
     * @return PDO
     * @throws Exception
     */
    protected function tryAgainIfCausedByLostConnection( Exception $exception, $dsn, $username, $password, $options )
    {
        if ( $this->causedByLostConnection( $exception ) ) {
            return $this->createPdoConnection( $dsn, $username, $password, $options );
        }

        throw $exception;
    }

    /**
     * 获取连接选线
     *
     * @param array $config
     *
     * @return array|mixed
     */
    public function getOptions( array $config )
    {
        $options = isset( $config[ 'options' ] ) ? $config[ 'options' ] : [];

        return array_diff_key( $this->options, $options ) + $options;
    }

}