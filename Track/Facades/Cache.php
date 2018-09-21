<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 16:53
 */

namespace Track\Facades;

/**
 * Class Cache
 *
 * @method static has( $key )
 * @method static get( $key, $default = null )
 * @method static many( array $keys )
 * @method static pull( $key, $default = null )
 * @method static set( $key, $value, $minutes = null )
 * @method static put( $key, $value, $minutes )
 * @method static putMany( array $values, $minutes )
 * @method static add( $key, $value, $minutes )
 * @method static increment( $key, $value = 1 )
 * @method static decrement( $key, $value = 1 )
 * @method static forever( $key, $value )
 * @method static forget( $key )
 * @method static delete( $key )
 * @method static clear()
 * @method static \Track\Cache\Repository store( $name = null )
 *
 * // redis 原生方法
 * @method static lPush( $key, $value1, $value2 = null, $valueN = null )
 * @method static brPop( array $keys, $timeout )
 *
 * @package Track\Facades
 *
 */
class Cache extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessName()
    {
        return 'cache';
    }
}