<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 13:59
 */

namespace Track\Cache;


interface RepositoryInterface
{
    /**
     * 缓存 key 是否存在
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has( $key );

    /**
     * 通过缓存 key 获取缓存值
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get( $key, $default = null );

    /**
     *  通过缓存 key 获取缓存值并删除此缓存项
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function pull( $key, $default = null );


    public function set( $key, $value, $ttl = null );

    /**
     * 设置 key 对应的 value
     *
     * @param  string                                     $key
     * @param  mixed                                      $value
     * @param  \DateTimeInterface|\DateInterval|float|int $seconds
     *
     * @return void
     */
    public function put( $key, $value, $seconds );

    /**
     * 如果 key 不存在,那么就设置缓存项
     *
     * @param  string                                     $key
     * @param  mixed                                      $value
     * @param  \DateTimeInterface|\DateInterval|float|int $seconds
     *
     * @return bool
     */
    public function add( $key, $value, $seconds );

    /**
     *  自增
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return int|bool
     */
    public function increment( $key, $value = 1 );

    /**
     *  自减
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return int|bool
     */
    public function decrement( $key, $value = 1 );

    /**
     * 设置缓存项且无过期时间
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever( $key, $value );

    /**
     * 删除缓存项
     *
     * @param  string $key
     *
     * @return bool
     */
    public function forget( $key );

    /**
     * 删除缓存项
     *
     * @param $key
     *
     * @return bool
     */
    public function delete( $key );

    /**
     * @return mixed
     */
    public function clear();

    /**
     * 回去当前缓存的实现,如 redis, memcache 等
     *
     * @return Store
     */
    public function getStore();
}