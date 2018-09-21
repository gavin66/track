<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 10:52
 */

namespace Track\Facades;


use Track\Container\ContainerContract as Container;
use RuntimeException;

abstract class Facade
{
    /**
     * 容器
     *
     * @var Container
     */
    protected static $container;

    /**
     * 已解析的门面实例
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * 获取容器实例
     *
     * @return Container
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * 设置容器
     *
     * @param Container $container
     */
    public static function setContainer( $container )
    {
        self::$container = $container;
    }

    /**
     * 获取当前门面对应的实例
     *
     * @return mixed
     */
    public static function getFacadeInstance()
    {
        return static::resolveFacadeInstance( static::getFacadeAccessName() );
    }

    /**
     * 获取门面名对应的实例,从容器中拿出或解析
     *
     * @param $name
     *
     * @return mixed
     */
    public static function resolveFacadeInstance( $name )
    {
        if ( is_object( $name ) ) {
            return $name;
        }
        if ( isset( static::$resolvedInstance[ $name ] ) ) {
            return static::$resolvedInstance[ $name ];
        }

        return static::$resolvedInstance[ $name ] = static::$container[ $name ];
    }

    /**
     * 获取在容器中注册的服务名
     *
     * @return string
     * @throws RuntimeException
     */
    protected static function getFacadeAccessName()
    {
        throw new RuntimeException( '没有实现 getFacadeAccessName 方法' );
    }

    /**
     * 使用静态魔术方法,调用当前门面对应实例的方法
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic( $name, $arguments )
    {
        $instance = static::getFacadeInstance();

        if ( ! $instance ) {
            throw new RuntimeException( '门面不能解析出实例' );
        }

        return $instance->$name( ...$arguments );
    }
}