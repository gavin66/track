<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/25
 * Time: 11:53
 */

namespace Track\Session;


use Track\Support\Arr;
use Track\Support\Str;

class Store implements SessionContract
{
    /**
     * session ID
     *
     * @var string
     */
    protected $id;

    /**
     * cookie 名
     *
     * @var string
     */
    protected $name;

    /**
     * session 属性
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * session 的处理实现
     *
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * session 开始标志
     *
     * @var bool
     */
    protected $started = false;


    /**
     * Store constructor.
     *
     * @param                          $name
     * @param \SessionHandlerInterface $handler
     * @param null                     $id
     */
    public function __construct( $name, \SessionHandlerInterface $handler, $id = null )
    {
        $this->setId( $id );
        $this->name    = $name;
        $this->handler = $handler;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function start()
    {
        $this->attributes = array_merge( $this->attributes, $this->readFromHandler() );

        // CSRF 保护
        if ( ! $this->has( '_token' ) ) {
            $this->put( '_token', Str::random( 40 ) );
        }

        return $this->started = true;
    }

    /**
     * 从 sessionHandler 中获取存储的 session 值
     *
     * @return array
     */
    protected function readFromHandler()
    {
        if ( $data = $this->handler->read( $this->getId() ) ) {
            $data = @unserialize( $data );

            if ( $data !== false && ! is_null( $data ) && is_array( $data ) ) {
                return $data;
            }
        }

        return [];
    }

    /**
     * cookie 名
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取 session ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 设置 session ID
     *
     * @param string $id
     */
    public function setId( $id )
    {
        $this->id = $this->isValidId( $id ) ? $id : $this->generateSessionId();
    }

    /**
     * session 保存
     *
     * @return void
     */
    public function save()
    {
        $this->handler->write( $this->getId(), serialize( $this->attributes ) );

        $this->started = false;
    }

    /**
     * 获取 session 数据
     *
     * @return array
     */
    public function all()
    {
        return $this->attributes;
    }

    /**
     * 是否存在 session key
     *
     * @param array|string $key
     *
     * @return bool
     */
    public function exists( $key )
    {
        return ! collect( is_array( $key ) ? $key : func_get_args() )->contains( function ( $key ) {
            return ! Arr::exists( $this->attributes, $key );
        } );
    }

    /**
     * 是否存在 session key,并且不是 null
     *
     * @param array|string $key
     *
     * @return bool
     */
    public function has( $key )
    {
        return ! collect( is_array( $key ) ? $key : func_get_args() )->contains( function ( $key ) {
            return is_null( $this->get( $key ) );
        } );
    }

    /**
     * 获取 session 中的值
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function get( $key, $default = null )
    {
        return Arr::get( $this->attributes, $key, $default );
    }

    /**
     * key / value 设置 session
     *
     * @param array|string $key
     * @param null         $value
     */
    public function put( $key, $value = null )
    {
        if ( ! is_array( $key ) ) {
            $key = [ $key => $value ];
        }

        foreach ( $key as $arrayKey => $arrayValue ) {
            Arr::set( $this->attributes, $arrayKey, $arrayValue );
        }
    }

    /**
     * 获取 CSRF token
     *
     * @return mixed|string
     */
    public function token()
    {
        return $this->get( '_token' );
    }

    /**
     * 获取value,并删除
     *
     * @param string $key
     *
     * @return mixed
     */
    public function remove( $key )
    {
        return Arr::pull( $this->attributes, $key );
    }

    /**
     * 移除一个或多个 session
     *
     * @param array|string $keys
     */
    public function forget( $keys )
    {
        Arr::forget( $this->attributes, $keys );
    }

    /**
     * 移除所有 session
     */
    public function flush()
    {
        $this->attributes = [];
    }

    /**
     * 生成新的 session ID
     *
     * @param bool $destroy 是否删除旧的 session
     *
     * @return bool
     */
    public function migrate( $destroy = false )
    {
        if ( $destroy ) {
            $this->handler->destroy( $this->getId() );
        }

        $this->setId( $this->generateSessionId() );

        return true;
    }

    /**
     *  删除 session,重新生成 session ID
     *
     * @return bool
     */
    public function invalidate()
    {
        $this->flush();

        return $this->migrate( true );
    }

    /**
     * session 是否已开始
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * 获取 session 处理实现
     *
     * @return \SessionHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * session ID 是否规范
     *
     * @param $id
     *
     * @return bool
     */
    public function isValidId( $id )
    {
        return is_string( $id ) && ctype_alnum( $id ) && strlen( $id ) === 40;
    }

    /**
     * 生成随机 session ID
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return Str::random( 40 );
    }

}