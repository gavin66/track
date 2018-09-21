<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 16:53
 */

namespace Track\Facades;

/**
 * Class WeChat
 *
 */
class WeChat extends Facade
{
    /**
     * 默认微信开放平台
     *
     * @return string
     */
    protected static function getFacadeAccessName()
    {
        return 'wechat.open_platform';
    }

    /**
     *  微信开放平台
     *
     * @return \Track\WeChat\OpenPlatform\OpenPlatform
     */
    public static function openPlatform()
    {
        return static::resolveFacadeInstance( 'wechat.open_platform' );
    }
}