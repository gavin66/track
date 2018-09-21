<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/17
 * Time: 17:57
 */

namespace Track\WeChat\Foundation;

use Track\Facades\Log;
use Track\Facades\Request;
use Track\Http\Response;
use Track\Support\Arr;

/**
 *  微信消息的验证,加解密
 *
 * @package Track\WeChat\Foundation
 */
class Defender
{
    /**
     * @var \Track\WeChat\Foundation\WeChat
     */
    protected $client;

    public function __construct( WeChat $client )
    {
        $this->client = $client;
    }

    /**
     * 接受微信消息请求并处理返回响应
     *
     * @param \Closure $resolve
     *  {"ToUserName":"gh_f5a01c7d7c0b","FromUserName":"oXDlE58Swau4FJh4EmZ4_uSy_1r8","CreateTime":"1535102398","MsgType":"text","Content":"xxxxxxxxxx","MsgId":"6593214595851682697"}
     *
     * @return \Track\Http\Response
     */
    public function serve( \Closure $resolve = null )
    {
        Log::debug( 'wechat_open_platform', [
            'method'       => Request::getMethod(),
            'uri'          => Request::getUri(),
            'content-type' => Request::getContentType(),
            'content'      => Request::getContent(),
        ] );

        return $this->validate()->resolve( $resolve );
    }

    /**
     * 验证请求签名正确性
     *
     * @return $this
     */
    protected function validate()
    {
        if ( ! $this->isEncrypt() ) {
            return $this;
        }

        if ( Request::input( 'signature' ) !== $this->signature( [
                $this->getToken(),
                Request::input( 'timestamp' ),
                Request::input( 'nonce' ),
            ] ) ) {
            throw new \RuntimeException( '请求签名无效' );
        }

        return $this;
    }

    /**
     * 业务处理
     *
     * @param \Closure $resolve
     *
     * @return \Track\Http\Response
     */
    protected function resolve( \Closure $resolve )
    {
        $message = $this->getMessage();

//        $to = Arr::get($message,'FromUserName','');
//        $from = Arr::get($message,'ToUserName','');

        if ( $resolve && is_callable( $resolve ) ) {
            // 返回响应
            $responseStr = $resolve( $message );
        }

        if ( empty( $responseStr ) || $responseStr === 'success' ) {
            $responseStr = 'success';
        } else {
            // 加密 xml
            $responseStr = $this->client->getService( 'encryptor' )->encrypt( $responseStr );
        }

        return new Response( $responseStr, 200 );
    }

    /**
     * 获取请求内容(解密后的)
     *
     * @return array
     */
    protected function getMessage()
    {
        $message = $this->parseMessage( Request::getContent() );

        if ( ! is_array( $message ) || empty( $message ) ) {
            throw new \RuntimeException( '内容是空的' );
        }

        if ( $this->isEncrypt() && ! empty( $message[ 'Encrypt' ] ) ) {
            $message = $this->client->getService( 'encryptor' )->decrypt(
                $message[ 'Encrypt' ],
                Request::input( 'msg_signature' ),
                Request::input( 'nonce' ),
                Request::input( 'timestamp' )
            );

            $dataSet = json_decode( $message, true );
            if ( $dataSet && ( JSON_ERROR_NONE === json_last_error() ) ) {
                return $dataSet;
            }

            $message = XML::parse( $message );
        }

        return $message;
    }

    /**
     * 解析请求内容体
     *
     * @param $content
     *
     * @return array
     */
    protected function parseMessage( $content )
    {
        try {
            if ( 0 === stripos( $content, '<' ) ) {
                $content = XML::parse( $content );
            } else {
                $dataSet = json_decode( $content, true );
                if ( $dataSet && ( JSON_ERROR_NONE === json_last_error() ) ) {
                    $content = $dataSet;
                }
            }

            return (array)$content;
        } catch ( \Exception $e ) {
            throw new \RuntimeException( sprintf( '内容解析失败:(%s) %s', $e->getCode(), $e->getMessage() ), $e->getCode() );
        }
    }

    /**
     * 获取消息校验 token,微信端配置
     *
     * @return string|null
     */
    protected function getToken()
    {
        return $this->client->getConfig()[ 'token' ];
    }

    /**
     * 请求的是否是加密数据
     *
     * @return bool
     */
    protected function isEncrypt()
    {
        return Request::input( 'signature' ) && 'aes' === Request::input( 'encrypt_type' );
    }

    /**
     * 生成 signature
     *
     * @param array $params
     *
     * @return string
     */
    protected function signature( array $params )
    {
        sort( $params, SORT_STRING );

        return sha1( implode( $params ) );
    }

    /**
     * todo 未完成
     *
     * @param $to
     * @param $from
     * @param $message
     *
     * @return string
     */
    public function buildResponseContent( $to, $from, $message )
    {
        if ( empty( $message ) || $message === 'success' ) {
            return 'success';
        }

//        if (is_string($message) || is_numeric($message)) {
//            $message = new Text((string) $message);
//        }
    }
}