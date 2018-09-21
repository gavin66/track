<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 10:52
 */

namespace Track\Facades;

/**
 * request 门面
 *
 * @method static input( $key = null, $default = null )
 * @method static all( $keys = null )
 * @method static has( $key )
 * @method static exists( $key )
 * @method static header( $key = null, $default = null )
 * @method static method()
 * @method static getRealMethod()
 * @method static getContent()
 * @method static string path()
 *
 * @method static string getClientIp()
 *
 * @package Track\Facades
 *
 * @see     \Track\Http\Request
 * @see     \Symfony\Component\HttpFoundation\Request
 */
class Request extends Facade
{
    /**
     * 服务名
     *
     * @return string
     */
    protected static function getFacadeAccessName()
    {
        return 'request';
    }
}