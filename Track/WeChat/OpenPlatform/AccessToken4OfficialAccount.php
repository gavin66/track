<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/16
 * Time: 13:41
 */

namespace Track\WeChat\OpenPlatform;

use Track\WeChat\Foundation\AccessToken as BaseAccessToken;
use Track\WeChat\OfficialAccount\OfficialAccount;


class AccessToken4OfficialAccount extends BaseAccessToken
{
    /**
     * 公众号
     *
     * @var array
     */
    protected $openPlatformConfig;

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var
     */
    protected $queryName = 'access_token';

    /**
     * @var string
     */
    protected $tokenKey = 'authorizer_access_token';

    /**
     * @var string
     */
    protected $cachePrefix = 'wechat.open_platform.official_access_token';

    /**
     * @var OpenPlatform
     */
    protected $openPlatform;

    /**
     * AccessToken4OfficialAccount constructor.
     *
     * @param \Track\WeChat\OfficialAccount\OfficialAccount $officialAccount
     * @param \Track\WeChat\OpenPlatform\OpenPlatform       $openPlatform
     */
    public function __construct( OfficialAccount $officialAccount, OpenPlatform $openPlatform )
    {
        parent::__construct( $officialAccount );

        $this->openPlatform = $openPlatform;
    }

    /**
     * 获取请求 token 时的附带参数
     *
     * @return array
     */
    protected function getRequestData()
    {
        return [
            'component_appid'          => $this->openPlatform->getConfig()[ 'app_id' ], // 开放平台 app_id
            'authorizer_appid'         => $this->WeChat->getConfig()[ 'app_id' ], // 公众号 app_id
            'authorizer_refresh_token' => $this->WeChat->getConfig()[ 'refresh_token' ], // 公众号
        ];
    }

    /**
     * 请求路径
     *
     * @return string
     */
    public function getPath()
    {
        return 'cgi-bin/component/api_authorizer_token?' . http_build_query( [
                'component_access_token' => $this->openPlatform->getService( 'access_token' )->getToken(),
            ] );
    }
}