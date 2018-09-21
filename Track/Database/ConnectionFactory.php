<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/30
 * Time: 11:52
 */

namespace Track\Database;


use Track\Container\ContainerContract as Container;
use Track\Database\Connections\Connection;
use Track\Database\Connections\MySqlConnection;
use Track\Database\Connectors\ConnectorInterface;
use Track\Database\Connectors\MySqlConnector;
use Track\Support\Arr;

class ConnectionFactory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * ConnectionFactory constructor.
     *
     * @param Container $container
     */
    public function __construct( Container $container )
    {
        $this->container = $container;
    }

    /**
     * 根据配置创建 PDO 连接
     *
     * @param array $config
     * @param       $name
     *
     * @return Connection
     */
    public function make( array $config, $name )
    {
        $config = Arr::add( $config, 'name', $name );

        $pdo = $this->createPdo( $config );

        return $this->createConnection( $config[ 'driver' ], $pdo, $config[ 'database' ], $config[ 'prefix' ], $config );
    }

    /**
     * 创建新 PDO 实例的闭包
     *
     * @param array $config
     *
     * @return \Closure
     */
    public function createPdo( array $config )
    {
        return function () use ( $config ) {
            return $this->createConnector( $config )->connect( $config );
        };
    }

    /**
     * 生成一个连接实例
     *
     * @param  string       $driver
     * @param \PDO|\Closure $connection
     * @param string        $database
     * @param string        $prefix
     * @param array         $config
     *
     * @return Connection
     */
    protected function createConnection( $driver, $connection, $database, $prefix = '', array $config = [] )
    {
//        if($resolver = Connection::getResolver($driver)){
//            return $resolver($connection, $database, $prefix, $config);
//        }

        switch ( $driver ) {
            case 'mysql':
                return new MySqlConnection( $connection, $database, $prefix, $config );
//            case 'pgsql':
//                return 'pgsql';
//            case 'sqlite':
//                return 'sqlite';
        }

        throw new \InvalidArgumentException( "不支持的驱动 [$driver]" );
    }

    /**
     * 根据配置获取 PDO 连接器
     *
     * @param array $config
     *
     * @return ConnectorInterface
     */
    public function createConnector( array $config )
    {
        if ( ! isset( $config[ 'driver' ] ) ) {
            throw new \InvalidArgumentException( '未指定 driver' );
        }

        if ( $this->container->bound( $key = "db.connector.{$config['driver']}" ) ) {
            return $this->container->make( $key );
        }

        switch ( $config[ 'driver' ] ) {
            case 'mysql':
                return new MySqlConnector;
//            case 'pgsql':
//                return new PostgresConnector;
//            case 'sqlite':
//                return new SQLiteConnector;
        }

        throw new \InvalidArgumentException( "不支持的驱动 [{$config['driver']}]" );
    }

}