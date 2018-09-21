<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/17
 * Time: 18:31
 */

namespace Track\WeChat\Foundation;


use Track\Support\Str;

class Encryptor
{
    const ERROR_INVALID_SIGNATURE = -40001; // 验证签名未通过
    const ERROR_INVALID_APP_ID = -40002; // 无效的 appId
    const ERROR_ENCRYPT_AES = -40003; // AES 加密错误

    /**
     * 微信端 appId
     *
     * @var string
     */
    protected $appId;

    /**
     * 消息校验 token
     *
     * @var string
     */
    protected $token;

    /**
     * 消息加解密 key
     *
     * @var string
     */
    protected $aesKey;

    /**
     * Block size.
     *
     * @var int
     */
    protected $blockSize = 32;

    /**
     * Constructor.
     *
     * @param string      $appId
     * @param string|null $token
     * @param string|null $aesKey
     */
    public function __construct( $appId, $token = null, $aesKey = null )
    {
        $this->appId  = $appId;
        $this->token  = $token;
        $this->aesKey = base64_decode( $aesKey . '=', true );
    }

    /**
     * 获取消息校验 token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 加密
     *
     * @param string $xml
     * @param string $nonce
     * @param int    $timestamp
     *
     * @return string
     */
    public function encrypt( $xml, $nonce = null, $timestamp = null )
    {
        try {
            $xml = $this->pkcs7Pad( Str::random( 16 ) . pack( 'N', strlen( $xml ) ) . $xml . $this->appId, $this->blockSize );

            $encrypted = base64_encode( AES::encrypt(
                $xml,
                $this->aesKey,
                substr( $this->aesKey, 0, 16 ),
                OPENSSL_NO_PADDING
            ) );
        } catch ( \Throwable $e ) {
            throw new \RuntimeException( $e->getMessage(), self::ERROR_ENCRYPT_AES );
        }

        ! is_null( $nonce ) || $nonce = substr( $this->appId, 0, 10 );
        ! is_null( $timestamp ) || $timestamp = time();

        $response = [
            'Encrypt'      => $encrypted,
            'MsgSignature' => $this->signature( $this->token, $timestamp, $nonce, $encrypted ),
            'TimeStamp'    => $timestamp,
            'Nonce'        => $nonce,
        ];

        //生成响应xml
        return XML::build( $response );
    }

    /**
     * 解密
     *
     * @param string $content
     * @param string $msgSignature
     * @param string $nonce
     * @param string $timestamp
     *
     * @return string
     *
     */
    public function decrypt( $content, $msgSignature, $nonce, $timestamp )
    {
        $signature = $this->signature( $this->token, $timestamp, $nonce, $content );

        if ( $signature !== $msgSignature ) {
            throw new \RuntimeException( '无效的签名', self::ERROR_INVALID_SIGNATURE );
        }

        $decrypted  = AES::decrypt(
            base64_decode( $content, true ),
            $this->aesKey,
            substr( $this->aesKey, 0, 16 ),
            OPENSSL_NO_PADDING
        );
        $result     = $this->pkcs7Unpad( $decrypted );
        $content    = substr( $result, 16, strlen( $result ) );
        $contentLen = unpack( 'N', substr( $content, 0, 4 ) )[ 1 ];

        if ( trim( substr( $content, $contentLen + 4 ) ) !== $this->appId ) {
            throw new \RuntimeException( '无效的 appId', self::ERROR_INVALID_APP_ID );
        }

        return substr( $content, 4, $contentLen );
    }

    /**
     * Get SHA1.
     *
     * @return string
     *
     * @throws self
     */
    public function signature()
    {
        $array = func_get_args();
        sort( $array, SORT_STRING );

        return sha1( implode( $array ) );
    }

    /**
     * PKCS#7 pad.
     *
     * @param string $text
     * @param int    $blockSize
     *
     * @return string
     */
    public function pkcs7Pad( $text, $blockSize )
    {
        if ( $blockSize > 256 ) {
            throw new \RuntimeException( '$blockSize may not be more than 256' );
        }
        $padding = $blockSize - ( strlen( $text ) % $blockSize );
        $pattern = chr( $padding );

        return $text . str_repeat( $pattern, $padding );
    }

    /**
     * PKCS#7 unpad.
     *
     * @param string $text
     *
     * @return string
     */
    public function pkcs7Unpad( $text )
    {
        $pad = ord( substr( $text, -1 ) );
        if ( $pad < 1 || $pad > $this->blockSize ) {
            $pad = 0;
        }

        return substr( $text, 0, ( strlen( $text ) - $pad ) );
    }
}