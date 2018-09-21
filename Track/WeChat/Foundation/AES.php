<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/17
 * Time: 18:40
 */

namespace Track\WeChat\Foundation;


class AES
{
    /**
     * @param string $text
     * @param string $key
     * @param string $iv
     * @param int    $option
     *
     * @return string
     */
    public static function encrypt( $text, $key, $iv, $option = OPENSSL_RAW_DATA )
    {
        self::validateKey( $key );
        self::validateIv( $iv );

        return openssl_encrypt( $text, self::getMode( $key ), $key, $option, $iv );
    }

    /**
     * @param string      $cipherText
     * @param string      $key
     * @param string      $iv
     * @param int         $option
     * @param string|null $method
     *
     * @return string
     */
    public static function decrypt( $cipherText, $key, $iv, $option = OPENSSL_RAW_DATA, $method = null )
    {
        self::validateKey( $key );
        self::validateIv( $iv );

        return openssl_decrypt( $cipherText, $method ? : self::getMode( $key ), $key, $option, $iv );
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public static function getMode( $key )
    {
        return 'aes-' . ( 8 * strlen( $key ) ) . '-cbc';
    }

    /**
     * @param string $key
     */
    public static function validateKey( $key )
    {
        if ( ! in_array( strlen( $key ), [ 16, 24, 32 ], true ) ) {
            throw new \InvalidArgumentException( sprintf( 'Key length must be 16, 24, or 32 bytes; got key len (%s).', strlen( $key ) ) );
        }
    }

    /**
     * @param string $iv
     *
     * @throws \InvalidArgumentException
     */
    public static function validateIv( $iv )
    {
        if ( ! empty( $iv ) && 16 !== strlen( $iv ) ) {
            throw new \InvalidArgumentException( 'IV length must be 16 bytes.' );
        }
    }
}