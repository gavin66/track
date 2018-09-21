<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/19
 * Time: 14:59
 */

namespace Track\Encryption;

use Track\Foundation\ServiceProvider;
use Track\Support\Str;

class EncryptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->container->singleton( 'encrypter', function () {
            $config = $this->container[ 'config' ][ 'app' ];

            if ( empty( $key = $config[ 'key' ] ) ) {
                throw new \RuntimeException( '加密密钥未设置' );
            }

            if ( Str::startsWith( $key, 'base64:' ) ) {
                $key = base64_decode( substr( $key, 7 ) );
            }

            return new Encrypter( $key, $config[ 'cipher' ] );
        } );
    }

}