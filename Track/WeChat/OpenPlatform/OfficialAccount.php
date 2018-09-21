<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/21
 * Time: 11:39
 */

namespace Track\WeChat\OpenPlatform;


use Track\Container\ContainerContract;
use Track\WeChat\Foundation\WeChat;
use Track\WeChat\OfficialAccount\OfficialAccount as BaseOfficialAccount;

/**
 * Class OfficialAccount
 *
 * @package Track\WeChat\OfficialAccount
 */
class OfficialAccount extends BaseOfficialAccount
{
    protected $servicePrefix = 'openPlatform_officialAccount';

    /**
     * @var \Track\WeChat\OpenPlatform\OpenPlatform
     */
    protected $openPlatform;

    public function __construct( ContainerContract $container, array $config, WeChat $openPlatform )
    {
        $this->openPlatform = $openPlatform;

        // 设置当前服务的服务提供者
        $this->mergeProviders( self::providers() );

        // 初始化当前服务
        parent::__construct( $container, $config );
    }

    protected function providers()
    {
        return [
            function ( ContainerContract $container ) {
                // access_token
                $container[ $this->getServiceName( 'access_token' ) ] = function () {
                    return new AccessToken4OfficialAccount( $this, $this->openPlatform );
                };
                // 消息加解密
                $container[ $this->getServiceName( 'encryptor' ) ] = function () {
                    return $this->openPlatform->getService( 'encryptor' );
                };
                // http client
                $container[ $this->getServiceName( 'http_client' ) ] = function () {
                    return $this->openPlatform->getService( 'http_client' );
                };
                // server
                $container[ $this->getServiceName( 'server' ) ] = function () {
                    return new Defender4OfficialAccount( $this );
                };
            },
        ];
    }

}