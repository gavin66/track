<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/15
 * Time: 11:19
 */

namespace Track\WeChat\OpenPlatform;


use Track\Container\ContainerContract;
use Track\Facades\Request;
use Track\WeChat\Foundation\WeChat;
use Track\WeChat\Foundation\Encryptor;

/**
 * Class OpenPlatform
 *
 * @property \Track\WeChat\OpenPlatform\Defender $server
 *
 * @package Track\WeChat\OpenPlatform
 */
class OpenPlatform extends WeChat
{
    protected $servicePrefix = 'open_platform';

    public function __construct( ContainerContract $container, array $config = [] )
    {
        // 设置当前服务的服务提供者
        $this->setProviders( $this->providers() );

        // 初始化当前服务
        parent::__construct( $container, $config );
    }

    /**
     * 开放平台提供的服务
     *
     * @return array
     */
    protected function providers()
    {
        return [
            function ( ContainerContract $container ) {
                // component_verify_ticket
                $container[ $this->getServiceName( 'verify_ticket' ) ] = function () {
                    return new VerifyTicket( $this );
                };
                // component_access_token
                $container[ $this->getServiceName( 'access_token' ) ] = function () {
                    return new AccessToken( $this );
                };
                // 消息服务
                $container[ $this->getServiceName( 'server' ) ] = function () {
                    return new Defender( $this );
                };
                // 消息加解密
                $container[ $this->getServiceName( 'encryptor' ) ] = function () {
                    return new Encryptor( $this->config[ 'app_id' ], $this->config[ 'token' ], $this->config[ 'aes_key' ] );
                };
            },
        ];
    }

    /**
     * 获取第三方授权登录页
     *
     * @param      $callbackUrl
     * @param null $preAuthCode
     *
     * @return string
     */
    public function getPreAuthorizationUrl( $callbackUrl, $preAuthCode = null )
    {
        $queries = [
            'component_appid' => $this->config[ 'app_id' ],
            'pre_auth_code'   => $preAuthCode ? : $this->createPreAuthorizationCode()[ 'pre_auth_code' ],
            'redirect_uri'    => $callbackUrl,
        ];

        return 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?' . http_build_query( $queries );
    }

    /**
     * 获取授权公众号
     *
     * @param string      $appId
     * @param string|null $refreshToken
     *
     * @return \Track\WeChat\OpenPlatform\OfficialAccount
     */
    public function officialAccount( $appId, $refreshToken = null )
    {
        return new OfficialAccount( $this->container, [ 'app_id' => $appId, 'refresh_token' => $refreshToken ], $this );
    }

    /**
     * 使用授权码换取接口调用凭据和授权信息
     *
     * @param string|null $authCode
     *
     * @return mixed
     */
    public function handleAuthorize( $authCode = null )
    {
        $params = [
            'component_appid'    => $this->config[ 'app_id' ],
            'authorization_code' => $authCode ? : Request::input( 'auth_code' ),
        ];

        return $this->httpPostJson( 'cgi-bin/component/api_query_auth', $params );
    }

    /**
     * 获取授权方的帐号基本信息
     *
     * @param string $appId
     *
     * @return mixed
     */
    public function getAuthorizer( $appId )
    {
        $params = [
            'component_appid'  => $this->config[ 'app_id' ],
            'authorizer_appid' => $appId,
        ];

        return $this->httpPostJson( 'cgi-bin/component/api_get_authorizer_info', $params );
    }

    /**
     * 获取授权方的选项设置信息
     *
     * @param string $appId
     * @param string $name
     *
     * @return mixed
     */
    public function getAuthorizerOption( $appId, $name )
    {
        $params = [
            'component_appid'  => $this->config[ 'app_id' ],
            'authorizer_appid' => $appId,
            'option_name'      => $name,
        ];

        return $this->httpPostJson( 'cgi-bin/component/api_get_authorizer_option', $params );
    }

    /**
     * 设置授权方的选项信息
     *
     * @param string $appId
     * @param string $name
     * @param string $value
     *
     * @return mixed
     */
    public function setAuthorizerOption( $appId, $name, $value )
    {
        $params = [
            'component_appid'  => $this->config[ 'app_id' ],
            'authorizer_appid' => $appId,
            'option_name'      => $name,
            'option_value'     => $value,
        ];

        return $this->httpPostJson( 'cgi-bin/component/api_set_authorizer_option', $params );
    }

    /**
     * 获取已授权的授权方列表
     *
     * @param int $offset
     * @param int $count
     *
     * @return mixed
     */
    public function getAuthorizers( $offset = 0, $count = 500 )
    {
        $params = [
            'component_appid' => $this->config[ 'app_id' ],
            'offset'          => $offset,
            'count'           => $count,
        ];

        return $this->httpPostJson( 'cgi-bin/component/api_get_authorizer_list', $params );
    }

    /**
     * Create pre-authorization code.
     *
     * @return \Psr\Http\Message\ResponseInterface|array|object|string
     */
    public function createPreAuthorizationCode()
    {
        $params = [
            'component_appid' => $this->config[ 'app_id' ],
        ];

        return $this->httpPostJson( 'cgi-bin/component/api_create_preauthcode', $params );
    }
}