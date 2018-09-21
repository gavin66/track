<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/20
 * Time: 11:49
 */

namespace Track\WeChat\OpenPlatform;

use \Track\WeChat\Foundation\Defender as WeChatDefender;

class Defender4OfficialAccount extends WeChatDefender
{
    protected function getToken()
    {
        return $this->client->getService( 'encryptor' )->getToken();
    }
}