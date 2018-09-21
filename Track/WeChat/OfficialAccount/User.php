<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/21
 * Time: 11:59
 */

namespace Track\WeChat\OfficialAccount;


use Track\WeChat\Foundation\HttpClient;

class User extends HttpClient
{
    /**
     * 更具 openid 获取用户信息
     *
     * @param        $openid
     * @param string $lang
     *
     * @return array
     */
    public function get( $openid, $lang = 'zh_CN' )
    {
        $params = [
            'openid' => $openid,
            'lang'   => $lang,
        ];

        return $this->httpGet( 'cgi-bin/user/info', $params );
    }

    /**
     * 获取用户列表
     *
     * @param null $nextOpenId
     *
     * @return array
     */
    public function listing($nextOpenId = null)
    {
        $params = ['next_openid' => $nextOpenId];

        return $this->httpGet('cgi-bin/user/get', $params);
    }
}