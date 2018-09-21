<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/24
 * Time: 15:03
 */

namespace Track\Database;


use Track\Application;
use Track\Database\Connections\Connection;
use Track\Support\Arr;

/**
 * Class DatabaseManager
 *
 * @mixin Connection
 * @package Track\Database
 */
class DatabaseManager
{
    /**
     * @var \Track\Application
     */
    protected $app;

    /**
     * 数据库连接工厂
     *
     * @var ConnectionFactory
     */
    protected $factory;

    /**
     * 所有的数据库连接
     *
     * @var array
     */
    protected $connections;


    public function __construct( Application $application, ConnectionFactory $factory )
    {
        $this->app     = $application;
        $this->factory = $factory;
    }

    /**
     * 获取一个数据库连接实例
     *
     * @param string $name
     *
     * @return Connection
     */
    public function connection( $name = null )
    {
        $name = $name ? : $this->getDefaultConnection();

        if ( ! isset( $this->connections[ $name ] ) ) {
            $this->connections[ $name ] = $this->configure( $this->makeConnection( $name ) );
        }

        return $this->connections[ $name ];
    }

    /**
     * 配置连接
     *
     * @param Connection $connection
     *
     * @return Connection
     */
    protected function configure( Connection $connection )
    {
        $connection->setReconnector( function ( $connection ) {
            $this->reconnect( $connection->getName() );
        } );

        return $connection;
    }

    /**
     * 创建连接
     *
     * @param $name
     *
     * @return Connection
     */
    protected function makeConnection( $name )
    {
        $config = $this->configuration( $name );

        return $this->factory->make( $config, $name );
    }

    /**
     * 根据键名获取数据库配置
     *
     * @param $name
     *
     * @return mixed
     */
    protected function configuration( $name )
    {
        $name = $name ? : $this->getDefaultConnection();

        $connections = $this->app[ 'config' ][ 'database.connections' ];

        if ( is_null( $config = Arr::get( $connections, $name ) ) ) {
            throw new \InvalidArgumentException( "数据库 [$name] 配置不存在" );
        }

        return $config;
    }

    /**
     * 关闭一个连接
     *
     * @param string $name
     */
    public function disconnect( $name = null )
    {
        if ( isset( $this->connections[ $name = $name ? : $this->getDefaultConnection() ] ) ) {
            $this->connections[ $name ]->disconnect();
        }
    }

    /**
     * 连接重连
     *
     * @param string $name
     *
     * @return mixed
     */
    public function reconnect( $name = null )
    {
        $this->disconnect( $name );

        // 如果没有连接,创建新的
        if ( ! isset( $this->connections[ $name ] ) ) {
            return $this->connection( $name );
        }

        // todo 这里理解错误
        return $this->connections[ $name ]->setPdo( $this->makeConnection( $name )->getPdo() );
    }

    /**
     * 默认连接
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->app[ 'config' ][ 'database.default' ];
    }

    /**
     * 动态调用默认连接
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call( $name, $arguments )
    {
        return $this->connection()->$name( ...$arguments );
    }


}