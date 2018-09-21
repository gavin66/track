<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 13:57
 */

namespace Track\Cache;


use Track\Container\ContainerContract as Container;
use InvalidArgumentException;

class CacheManager implements Factory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * 解析过的缓存实例
     *
     * @var array
     */
    protected $stores = [];

    public function __construct( Container $container )
    {
        $this->container = $container;
    }

    /**
     * 获取缓存实例
     *
     * @param null $name
     *
     * @return mixed|Repository
     */
    public function store( $name = null )
    {
        $name = $name ? : $this->getDefaultDriver();

        return $this->stores[ $name ] = $this->get( $name );
    }

    /**
     * 获取驱动实例
     *
     * @param null $driver
     *
     * @return mixed|Repository
     */
    public function driver( $driver = null )
    {
        return $this->store( $driver );
    }

    /**
     * 获取缓存实例
     *
     * @param $name
     *
     * @return mixed|Repository
     */
    protected function get( $name )
    {
        return isset( $this->stores[ $name ] ) ? $this->stores[ $name ] : $this->resolve( $name );
    }

    /**
     * 根据驱动名称解析实例
     *
     * @param $name
     *
     * @return Repository
     */
    protected function resolve( $name )
    {
        $config = $this->getConfig( $name );

        if ( is_null( $config ) ) {
            throw new InvalidArgumentException( "缓存 [$name] 的配置错误" );
        }

        $driverMethod = 'create' . ucfirst( $config[ 'driver' ] ) . 'Driver';

        if ( method_exists( $this, $driverMethod ) ) {
            return $this->{$driverMethod}( $config );
        } else {
            throw new InvalidArgumentException( "缓存 [{$config['driver']}] 是不支持的驱动" );
        }
    }

    protected function createRedisDriver( array $config )
    {
        $redis = new RedisStore( $config, $this->getPrefix( $config ) );

        return new Repository( $redis );
    }

    /**
     * 获取默认缓存驱动名
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container[ 'config' ][ 'cache.default' ];
    }

    /**
     * 获取驱动相对应的配置
     *
     * @param $name
     *
     * @return mixed
     */
    protected function getConfig( $name )
    {
        return $this->container[ 'config' ][ "cache.stores.{$name}" ];
    }

    /**
     * 获取缓存前缀
     *
     * @param array $config
     *
     * @return string
     */
    protected function getPrefix( array $config )
    {
        return isset( $config[ 'prefix' ] ) ? $config[ 'prefix' ] : $this->container[ 'config' ][ 'cache.prefix' ];
    }

    /**
     * 调用缓存方法
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call( $name, $arguments )
    {
        return $this->store()->$name( ...$arguments );
    }
}