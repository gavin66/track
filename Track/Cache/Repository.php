<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 14:36
 */

namespace Track\Cache;

use ArrayAccess;

class Repository implements RepositoryInterface, ArrayAccess
{

    /**
     * @var Store
     */
    protected $store;

    /**
     * 默认 60 分钟过期
     *
     * @var int
     */
    protected $default = 60;

    public function __construct( Store $store )
    {
        $this->store = $store;
    }

    /**
     * 缓存是否存在
     *
     * @param string $key
     *
     * @return bool
     */
    public function has( $key )
    {
        return ! is_null( $this->get( $key ) );
    }

    /**
     * 获取缓存值
     *
     * @param string $key
     * @param null   $default
     *
     * @return array|mixed
     */
    public function get( $key, $default = null )
    {
        if ( is_array( $key ) ) {
            return $this->many( $key );
        }

        $value = $this->store->get( $key );

        if ( is_null( $value ) ) {
            $value = value( $default );
        }

        return $value;
    }

    /**
     * 获取多个缓存值
     *
     * @param array $keys
     *
     * @return array
     */
    public function many( array $keys )
    {
        return $this->store->many( $keys );
    }

    /**
     * 获取缓存后删除
     *
     * @param string $key
     * @param null   $default
     *
     * @return bool
     */
    public function pull( $key, $default = null )
    {
        $this->get( $key, $default );

        return $this->forget( $key );
    }

    /**
     * 设置缓存
     *
     * @param      $key
     * @param      $value
     * @param null $minutes
     */
    public function set( $key, $value, $minutes = null )
    {
        $this->put( $key, $value, $minutes );
    }

    /**
     * 设置缓存
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     *
     * @return void
     */
    public function put( $key, $value, $minutes )
    {
        if ( is_array( $key ) ) {
            $this->putMany( $key, $value );
        }

        $this->store->put( $key, $value, $minutes );
    }

    /**
     * 使用数组,设置多个缓存
     *
     * @param array $values
     * @param       $minutes
     */
    public function putMany( array $values, $minutes )
    {
        $this->store->putMany( $values, $minutes );
    }

    /**
     * 缓存不存在,设置缓存
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     *
     * @return bool
     */
    public function add( $key, $value, $minutes )
    {
        if ( method_exists( $this->store, 'add' ) ) {
            return $this->store->add( $key, $value, $minutes );
        }

        if ( is_null( $this->get( $key ) ) ) {
            $this->put( $key, $value, $minutes );

            return true;
        }

        return false;
    }

    /**
     * 自增
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|int
     */
    public function increment( $key, $value = 1 )
    {
        return $this->store->increment( $key, $value );
    }

    /**
     * 自减
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|int
     */
    public function decrement( $key, $value = 1 )
    {
        return $this->store->decrement( $key, $value );
    }

    /**
     * 设置缓存,无过期时间
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever( $key, $value )
    {
        $this->store->forever( $key, $value );
    }

    /**
     * 删除缓存
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget( $key )
    {
        return $this->store->forget( $key );
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function delete( $key )
    {
        return $this->forget( $key );
    }

    /**
     * 清空所有缓存
     *
     * @return bool|mixed
     */
    public function clear()
    {
        return $this->store->flush();
    }

    /**
     * 缓存实例
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    public function offsetExists( $offset )
    {
        return $this->has( $offset );
    }

    public function offsetGet( $offset )
    {
        return $this->get( $offset );
    }

    public function offsetSet( $offset, $value )
    {
        $this->put( $offset, $value, $this->default );
    }

    public function offsetUnset( $offset )
    {
        $this->forget( $offset );
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call( $method, $parameters )
    {
        return $this->store->$method( ...$parameters );
    }

    public function __clone()
    {

    }
}