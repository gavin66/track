<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/19
 * Time: 11:22
 */

namespace Track\Encryption;


interface EncrypterContract
{
    /**
     * 加密
     *
     * @param  string $value
     * @param  bool   $serialize
     *
     * @return string
     */
    public function encrypt( $value, $serialize = true );

    /**
     * 解密
     *
     * @param  string $payload
     * @param  bool   $unserialize
     *
     * @return string
     */
    public function decrypt( $payload, $unserialize = true );
}