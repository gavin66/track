<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/20
 * Time: 11:49
 */

namespace Track\WeChat\OpenPlatform;

use Track\Http\Response;
use \Track\WeChat\Foundation\Defender as WeChatDefender;

class Defender extends WeChatDefender
{
    const EVENT_AUTHORIZED = 'authorized';
    const EVENT_UNAUTHORIZED = 'unauthorized';
    const EVENT_UPDATE_AUTHORIZED = 'updateauthorized';
    const EVENT_COMPONENT_VERIFY_TICKET = 'component_verify_ticket';

    /**
     * 业务处理
     *
     * @param \Closure $resolve
     *
     * @return \Track\Http\Response
     */
    protected function resolve( \Closure $resolve = null )
    {
        $message = $this->getMessage();

        if ( $resolve && is_callable( $resolve ) ) {
            $resolve( $message );
        }

        self::resolveVerifyTicket( $message );

        return new Response( 'success' );
    }

    /**
     * 缓存 component_verify_ticket
     *
     * @param array $message
     */
    protected function resolveVerifyTicket( array $message )
    {
        if ( $message[ 'InfoType' ] == self::EVENT_COMPONENT_VERIFY_TICKET ) {
            $this->client->getService( 'verify_ticket' )->setTicket( $message[ 'ComponentVerifyTicket' ] );
        }
    }
}