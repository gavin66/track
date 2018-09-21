<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/22
 * Time: 14:19
 */

namespace Track\Session;


use Track\Container\ContainerContract as Container;
use Track\Support\Str;

class SessionManager
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct( Container $container )
    {
        $this->container = $container;
    }

    /**
     * @var array
     */
    protected $drivers = [];

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container[ 'config' ][ 'session.driver' ];
    }

    /**
     * 获取 session 驱动实例
     *
     * @param string $driver
     *
     * @return SessionContract
     */
    public function driver( $driver = null )
    {
        $driver = $driver ? : $this->getDefaultDriver();

        if ( ! isset( $this->drivers[ $driver ] ) ) {
            $this->drivers[ $driver ] = $this->createDriver( $driver );
        }

        return $this->drivers[ $driver ];
    }

    /**
     * 创建相应驱动
     *
     * @param $driver
     *
     * @return mixed
     */
    public function createDriver( $driver )
    {
        $method = 'create' . Str::ucfirst( $driver ) . 'Driver';

        if ( method_exists( $this, $method ) ) {
            return $this->$method();
        }

        throw new \InvalidArgumentException( "驱动 [$driver] 不支持" );
    }

    /**
     * 创建 redis session 处理实例
     *
     * @return Store
     */
    protected function createRedisDriver()
    {
        $store = $this->container[ 'config' ][ 'session.store' ] ? : 'redis';

        $handler = new CacheBasedSessionHandler(
            clone $this->container[ 'cache' ]->store( $store ),
            $this->container[ 'config' ][ 'session.lifetime' ]
        );

        return new Store( $this->container[ 'config' ][ 'session.cookie' ], $handler );
    }

    /**
     * 获取 session 配置
     *
     * @return array
     */
    public function getSessionConfig()
    {
        return $this->container[ 'config' ][ 'session' ];
    }

    /**
     * 动态调用
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call( $name, $arguments )
    {
        return $this->driver()->$name( ...$arguments );
    }
}