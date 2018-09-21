<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 14:40
 */

namespace Track\Cache;

use Redis;

class RedisStore implements Store
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * 缓存 key 前缀
     *
     * @var string
     */
    protected $prefix;

    /**
     * RedisStore constructor.
     *
     * @param $config
     * @param $prefix
     */
    public function __construct( $config, $prefix )
    {
        $this->connection( $config );
        $this->setPrefix( $prefix );
    }

    /**
     * 获取 redis 连接实例
     *
     * @param $config
     *
     * @return Redis
     */
    public function connection( $config )
    {
        if ( ! $this->redis ) {
            $this->redis = new Redis();
            $this->redis->connect( $config[ 'host' ], $config[ 'port' ] );

            if ( ! empty( $config[ 'password' ] ) ) {
                $this->redis->auth( $config[ 'password' ] );
            }
            if ( ! empty( $config[ 'database' ] ) ) {
                $this->redis->select( $config[ 'database' ] );
            }
        }

        return $this->redis;
    }

    /**
     * 从缓存中获取值
     *
     * @param  string|array $key
     *
     * @return mixed
     */
    public function get( $key )
    {
        $value = $this->redis->get( $this->prefix . $key );

        return ! is_null( $value ) && $value !== false ? $value : null;
    }

    /**
     * 获取多个缓存值
     *
     * @param  array $keys
     *
     * @return array
     */
    public function many( array $keys )
    {
        $results = [];

        $values = $this->redis->mget( array_map( function ( $key ) {
            return $this->prefix . $key;
        }, $keys ) );

        foreach ( $values as $index => $value ) {
            $results[ $keys[ $index ] ] = ! is_null( $value ) ? $value : null;
        }

        return $results;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string    $key
     * @param  mixed     $value
     * @param  float|int $minutes
     *
     * @return void
     */
    public function put( $key, $value, $minutes )
    {
        $this->redis->setex(
            $this->prefix . $key, (int)max( 1, $minutes * 60 ), $value
        );
    }

    /**
     * 设置多个缓存值
     *
     * @param  array     $values
     * @param  float|int $minutes
     *
     * @return void
     */
    public function putMany( array $values, $minutes )
    {
        $this->redis->multi();

        foreach ( $values as $key => $value ) {
            $this->put( $key, $value, $minutes );
        }

        $this->redis->exec();
    }

    /**
     *  当缓存不存在时,设置缓存
     *
     * @param  string    $key
     * @param  mixed     $value
     * @param  float|int $minutes
     *
     * @return bool
     */
    public function add( $key, $value, $minutes )
    {
        $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('setex',KEYS[1],ARGV[2],ARGV[1])";

        return (bool)$this->redis->eval(
            $lua, 1, $this->prefix . $key, $value, (int)max( 1, $minutes * 60 )
        );
    }

    /**
     * 自增
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return int
     */
    public function increment( $key, $value = 1 )
    {
        return $this->redis->incrby( $this->prefix . $key, $value );
    }

    /**
     * 自减
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return int
     */
    public function decrement( $key, $value = 1 )
    {
        return $this->redis->decrby( $this->prefix . $key, $value );
    }

    /**
     * 设置缓存,无过期时间
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever( $key, $value )
    {
        $this->redis->set( $this->prefix . $key, $value );
    }

    /**
     * 删除指定缓存
     *
     * @param  string $key
     *
     * @return bool
     */
    public function forget( $key )
    {
        return (bool)$this->redis->del( $this->prefix . $key );
    }

    /**
     * 删除所有缓存
     *
     * @return bool
     */
    public function flush()
    {
        $this->redis->flushdb();

        return true;
    }

    /**
     * 获取 redis 实例
     *
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * 获取 key 前缀
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 设置 key 前缀
     *
     * @param  string $prefix
     *
     * @return void
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = ! empty( $prefix ) ? $prefix . ':' : '';
    }

    /**
     * 动态方法调用
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call( $name, $arguments )
    {
        return $this->redis->{$name}( ...$arguments );
    }
}