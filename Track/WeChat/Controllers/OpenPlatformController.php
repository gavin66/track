<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/15
 * Time: 10:57
 */

namespace Track\WeChat\Controllers;


use Track\Routing\Controller;
use Track\WeChat\OpenPlatform\OpenPlatform;

class OpenPlatformController extends Controller
{
    public function __invoke( OpenPlatform $openPlatform )
    {
        return $openPlatform->server->serve();
    }
}