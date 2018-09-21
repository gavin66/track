<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/16
 * Time: 11:28
 */

namespace Track\WeChat\OpenPlatform;

use Track\Facades\Cache;
use Track\WeChat\Foundation\WeChat;

class VerifyTicket
{
    /**
     * @var \Track\WeChat\Foundation\WeChat
     */
    protected $client;

    public function __construct( WeChat $client )
    {
        $this->client = $client;
    }

    /**
     * 设置 component_verify_ticket
     *
     * @param string $ticket
     *
     * @return $this
     */
    public function setTicket( $ticket )
    {
        Cache::set( $this->getCacheKey(), $ticket, 30 );

        return $this;
    }

    /**
     * 获取 component_verify_ticket
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getTicket()
    {
        if ( $cached = Cache::get( $this->getCacheKey() ) ) {
            return $cached;
        }

        throw new \RuntimeException( '未能获取 component_verify_ticket' );
    }

    /**
     * 缓存 key
     *
     * @return string
     */
    public function getCacheKey()
    {
        return 'wechat.open_platform.verify_ticket.' . $this->client->getConfig()[ 'app_id' ];
    }
}