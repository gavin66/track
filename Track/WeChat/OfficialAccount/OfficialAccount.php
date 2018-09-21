<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/21
 * Time: 11:39
 */

namespace Track\WeChat\OfficialAccount;


use Track\Container\ContainerContract;
use Track\WeChat\Foundation\WeChat;

/**
 * Class OfficialAccount
 *
 * @property \Track\WeChat\OfficialAccount\Menu     $menu
 * @property \Track\WeChat\OfficialAccount\Defender $server
 * @property \Track\WeChat\OfficialAccount\JsSdk    $jssdk
 * @property \Track\WeChat\OfficialAccount\User     $user
 *
 * @package Track\WeChat\OfficialAccount
 */
class OfficialAccount extends WeChat
{
    protected $servicePrefix = 'official_account';

    public function __construct( ContainerContract $container, array $config = [] )
    {
        // 设置当前服务的服务提供者
        $this->mergeProviders( self::providers() );

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
                // menu 菜单
                $container[ $this->getServiceName( 'menu' ) ]  = function () {
                    return new Menu( $this );
                };
                $container[ $this->getServiceName( 'jssdk' ) ] = function () {
                    return new JsSdk( $this );
                };
                $container[ $this->getServiceName( 'user' ) ]  = function () {
                    return new User( $this );
                };
            },
        ];
    }

}