<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/9
 * Time: 17:42
 */

namespace Track\Config;

use ArrayAccess;
use Track\Support\Arr;

/**
 * 所有配置文件存储处
 *
 * @package Track\Config
 */
class Repository implements ArrayAccess
{

    /**
     * 所有配置项
     *
     * @var array
     */
    protected $items = [];

    /**
     * 创建新配置项
     *
     * @param  array $items
     *
     * @return void
     */
    public function __construct( array $items = [] )
    {
        $this->items = $items;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has( $key )
    {
        return Arr::has( $this->items, $key );
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string $key
     * @param  mixed        $default
     *
     * @return mixed
     */
    public function get( $key, $default = null )
    {
        if ( is_array( $key ) ) {
            return $this->getMany( $key );
        }

        return Arr::get( $this->items, $key, $default );
    }

    /**
     * Get many configuration values.
     *
     * @param  array $keys
     *
     * @return array
     */
    public function getMany( $keys )
    {
        $config = [];

        foreach ( $keys as $key => $default ) {
            if ( is_numeric( $key ) ) {
                list( $key, $default ) = [ $default, null ];
            }

            $config[ $key ] = Arr::get( $this->items, $key, $default );
        }

        return $config;
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string $key
     * @param  mixed        $value
     *
     * @return void
     */
    public function set( $key, $value = null )
    {
        $keys = is_array( $key ) ? $key : [ $key => $value ];

        foreach ( $keys as $key => $value ) {
            Arr::set( $this->items, $key, $value );
        }
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function prepend( $key, $value )
    {
        $array = $this->get( $key );

        array_unshift( $array, $value );

        $this->set( $key, $array );
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function push( $key, $value )
    {
        $array = $this->get( $key );

        $array[] = $value;

        $this->set( $key, $array );
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
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
        $this->set( $offset, $value );
    }

    public function offsetUnset( $offset )
    {
        $this->set( $offset, null );
    }

}