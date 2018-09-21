<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/21
 * Time: 11:59
 */

namespace Track\WeChat\OfficialAccount;


use Track\Facades\Cache;
use Track\WeChat\Foundation\HttpClient;

class JsSdk extends HttpClient
{
    public function getTicket( $refresh = false, $type = 'jsapi' )
    {
        $cacheKey = sprintf( 'wechat.jssdk.ticket.%s.%s', $type, $this->getAppId() );

        if ( ! $refresh && Cache::has( $cacheKey ) ) {
            return Cache::get( $cacheKey );
        }

        $result = $this->httpGet( 'cgi-bin/ticket/getticket', [ 'type' => $type ] );

        Cache::set( $cacheKey, $result[ 'ticket' ], 90 );

        return $result[ 'ticket' ];
    }

    protected function getAppId()
    {
        return $this->WeChat->getConfig()[ 'app_id' ];
    }
}