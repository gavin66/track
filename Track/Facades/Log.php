<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 16:53
 */

namespace Track\Facades;

/**
 * Class Log
 *
 * @method static debug( $message, $context = [] )
 * @method static info( $message, $context = [] )
 * @method static notice( $message, $context = [] )
 * @method static warning( $message, $context = [] )
 * @method static error( $message, $context = [] )
 * @method static critical( $message, $context = [] )
 * @method static alert( $message, $context = [] )
 * @method static emergency( $message, $context = [] )
 *
 * @package Track\Facades
 */
class Log extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessName()
    {
        return 'log';
    }
}