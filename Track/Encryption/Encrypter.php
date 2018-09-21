<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/19
 * Time: 11:25
 */

namespace Track\Encryption;


class Encrypter implements EncrypterContract
{
    /**
     * 加密密钥
     *
     * @var string
     */
    protected $key;

    /**
     * 加密算法
     *
     * @var string
     */
    protected $cipher;

    /**
     * 创建密码生成器实例
     *
     * @param  string $key
     * @param  string $cipher
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function __construct( $key, $cipher = 'AES-128-CBC' )
    {
        $key = (string)$key;

        if ( static::supported( $key, $cipher ) ) {
            $this->key    = $key;
            $this->cipher = $cipher;
        } else {
            throw new \RuntimeException( 'AES-128-CBC 和 AES-256-CBC 的密钥长度为 16 与 32' );
        }
    }

    /**
     * 确定给定密钥与加密方式是否匹配
     *
     * @param  string $key
     * @param  string $cipher
     *
     * @return bool
     */
    public static function supported( $key, $cipher )
    {
        $length = mb_strlen( $key, '8bit' );

        return ( $cipher === 'AES-128-CBC' && $length === 16 ) || ( $cipher === 'AES-256-CBC' && $length === 32 );
    }

    /**
     * 生成随机加密密钥
     *
     * @param  string $cipher
     *
     * @return string
     * @throws \Exception
     */
    public static function generateKey( $cipher )
    {
        return openssl_random_pseudo_bytes( $cipher == 'AES-128-CBC' ? 16 : 32 );
    }

    /**
     * 加密
     *
     * @param  mixed $value
     * @param  bool  $serialize
     *
     * @return string
     *
     * @throws EncryptException
     * @throws \Exception
     */
    public function encrypt( $value, $serialize = true )
    {
        $iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( $this->cipher ) );

        // 使用 openssl 加密该值
        $value = \openssl_encrypt(
            $serialize ? serialize( $value ) : $value,
            $this->cipher, $this->key, 0, $iv
        );

        if ( $value === false ) {
            throw new EncryptException( '不能加密数据' );
        }

        // 计算加密值的消息认证码(MAC),以便之后验证加密值未被用户篡改
        $mac = $this->hash( $iv = base64_encode( $iv ), $value );

        // 组成信息数组并 json 化
        $json = json_encode( compact( 'iv', 'value', 'mac' ) );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new EncryptException( '不能加密数据' );
        }

        return base64_encode( $json );
    }

    /**
     * 加密字符串,不使用序列化
     *
     * @param  string $value
     *
     * @return string
     * @throws EncryptException
     * @throws \Exception
     */
    public function encryptString( $value )
    {
        return $this->encrypt( $value, false );
    }

    /**
     * 解密
     *
     * @param  mixed $payload
     * @param  bool  $unserialize
     *
     * @return mixed
     *
     * @throws DecryptException
     * @throws \Exception
     */
    public function decrypt( $payload, $unserialize = true )
    {
        // 验证有效性
        $payload = $this->getJsonPayload( $payload );

        $iv = base64_decode( $payload[ 'iv' ] );

        $decrypted = \openssl_decrypt(
            $payload[ 'value' ], $this->cipher, $this->key, 0, $iv
        );

        if ( $decrypted === false ) {
            throw new DecryptException( '不能解密数据' );
        }

        return $unserialize ? unserialize( $decrypted ) : $decrypted;
    }

    /**
     *  解密字符串,不使用序列化
     *
     * @param  string $payload
     *
     * @return string
     *
     * @throws DecryptException
     * @throws \Exception
     */
    public function decryptString( $payload )
    {
        return $this->decrypt( $payload, false );
    }

    /**
     * 创建给定值的消息认证码(MAC)
     *
     * @param  string $iv
     * @param  mixed  $value
     *
     * @return string
     */
    protected function hash( $iv, $value )
    {
        return hash_hmac( 'sha256', $iv . $value, $this->key );
    }

    /**
     *  获取数据(payload)的数组表示形式
     *
     * @param  string $payload
     *
     * @return array
     *
     * @throws DecryptException
     * @throws \Exception
     */
    protected function getJsonPayload( $payload )
    {
        $payload = json_decode( base64_decode( $payload ), true );

        // 验证 payload 是否有效
        if ( ! $this->validPayload( $payload ) ) {
            throw new DecryptException( '数据无效' );
        }

        // 验证 MAC 是否有效,数据未被篡改
        if ( ! $this->validMac( $payload ) ) {
            throw new DecryptException( '消息认证码无效' );
        }

        return $payload;
    }

    /**
     * 验证数据(payload)是否有效
     *
     * @param  mixed $payload
     *
     * @return bool
     */
    protected function validPayload( $payload )
    {
        return is_array( $payload ) && isset( $payload[ 'iv' ], $payload[ 'value' ], $payload[ 'mac' ] ) &&
            strlen( base64_decode( $payload[ 'iv' ], true ) ) === openssl_cipher_iv_length( $this->cipher );
    }

    /**
     *  确定给定的数据(payload)的消息认证码(MAC)是否正确
     *
     * @param  array $payload
     *
     * @return bool
     * @throws \Exception
     */
    protected function validMac( array $payload )
    {
        // 防止时序攻击
        return hash_equals( $payload[ 'mac' ], $this->hash( $payload[ 'iv' ], $payload[ 'value' ] ) );
    }

    /**
     * 获取加密密钥
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}