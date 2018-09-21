<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/16
 * Time: 13:41
 */

namespace Track\WeChat\OfficialAccount;

use Track\WeChat\Foundation\AccessToken as BaseAccessToken;


class AccessToken extends BaseAccessToken
{
    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var string
     */
    protected $tokenKey = 'component_access_token';

    /**
     * @var string
     */
    protected $path = 'cgi-bin/component/api_component_token';


    protected $cachePrefix = 'wechat.open_platform.component_access_token';

    /**
     * @return array
     */
    protected function getRequestData()
    {
        return [
            'component_appid'         => $this->WeChat->getConfig()[ 'app_id' ],
            'component_appsecret'     => $this->WeChat->getConfig()[ 'secret' ],
            'component_verify_ticket' => $this->WeChat->getService( 'verify_ticket' )->getTicket(),
        ];
    }
}