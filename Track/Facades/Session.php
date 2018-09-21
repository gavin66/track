<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 16:53
 */

namespace Track\Facades;

/**
 * Class Track\Session\Store
 * @method static string getName()
 * @method static string getId()
 * @method static array all()
 * @method static bool exists( $key )
 * @method static bool has( $key )
 * @method static get( $key, $default = null )
 * @method static put( $key, $value = null )
 * @method static string token()
 * @method static mixed remove( $key )
 * @method static forget( $keys )
 * @method static flush()
 * @method static bool migrate( $destroy = false )
 * @method static bool isStarted()
 * @method static \SessionHandlerInterface getHandler()
 * @method static bool isValidId( $id )
 *
 * @package Track\Facades
 *
 */
class Session extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessName()
    {
        return 'session';
    }
}