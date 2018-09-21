<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/16
 * Time: 13:54
 */

namespace Track\WeChat\Foundation;


interface AccessTokenContracts
{
    /**
     * @return array
     */
    public function getToken();

    /**
     * @return \Track\WeChat\Foundation\AccessTokenContracts
     */
    public function refresh();
}