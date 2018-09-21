<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/21
 * Time: 16:09
 */

namespace Track\WeChat\Foundation;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Track\Facades\Log;

class HttpClient
{
    /**
     * @var \Track\WeChat\Foundation\WeChat
     */
    protected $WeChat;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var \GuzzleHttp\HandlerStack
     */
    protected $handlerStack;

    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * 默认的配置项
     *
     * @var array
     */
    protected $config = [
        'http' => [
            'timeout'     => 5.0, // 过时
            'retries'     => 2, // 错误重试次数
            'retry_delay' => 500, // 重试延迟
            'base_uri'    => 'https://api.weixin.qq.com/',
        ],
    ];

    public function __construct( WeChat $weChat, array $config = [] )
    {
        $this->WeChat = $weChat;

        $this->config = array_merge( $this->config, $config );
    }

    /**
     * 发送请求
     *
     * @param        $url
     * @param string $method
     * @param array  $options
     * @param bool   $raw
     *
     * @return array|mixed|object|\Psr\Http\Message\ResponseInterface
     */
    public function request( $url, $method = 'GET', array $options = [], $raw = false )
    {
        if ( empty( $this->middlewares ) ) {
            $this->registerHttpMiddlewares();
        }

        $options = array_merge( self::$defaults, $options, [ 'handler' => $this->getHandlerStack() ] );

        $options = $this->fixJsonIssue( $options );

        $response = $this->getHttpClient()->request( strtoupper( $method ), $url, $options );

        // 初始化位置
        $response->getBody()->rewind();

        return $raw ? $response : $this->castResponseToType( $response, 'array' );
    }

    /**
     * GET 请求
     *
     * @param string $url
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|array|object|string
     */
    public function httpGet( $url, array $query = [] )
    {
        return $this->request( $url, 'GET', [ 'query' => $query ] );
    }

    /**
     * POST 请求
     *
     * @param string $url
     * @param array  $data
     *
     * @return \Psr\Http\Message\ResponseInterface|array|object|string
     */
    public function httpPost( $url, array $data = [] )
    {
        return $this->request( $url, 'POST', [ 'form_params' => $data ] );
    }

    /**
     * 发送 Json 请求
     *
     * @param string       $url
     * @param string|array $data
     * @param array        $query
     *
     * @return \Psr\Http\Message\ResponseInterface|array|object|string
     */
    public function httpPostJson( $url, array $data = [], array $query = [] )
    {
        return $this->request( $url, 'POST', [ 'query' => $query, 'json' => $data ] );
    }

    /**
     * 上传文件
     *
     * @param string $url
     * @param array  $files
     * @param array  $form
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|array|object|string
     */
    public function httpUpload( $url, array $files = [], array $form = [], array $query = [] )
    {
        $multipart = [];

        foreach ( $files as $name => $path ) {
            $multipart[] = [
                'name'     => $name,
                'contents' => fopen( $path, 'r' ),
            ];
        }

        foreach ( $form as $name => $contents ) {
            $multipart[] = compact( 'name', 'contents' );
        }

        return $this->request( $url, 'POST', [ 'query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30 ] );
    }

    /**
     * 注册 Guzzle 中间件
     */
    protected function registerHttpMiddlewares()
    {
        // 请求重试中间件
        $this->pushMiddleware( $this->retryMiddleware(), 'retry' );
        // access_token
        $this->pushMiddleware( $this->accessTokenMiddleware(), 'access_token' );
    }

    /**
     * 附加 access_token
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function ( callable $handler ) {
            return function ( RequestInterface $request, array $options ) use ( $handler ) {
                $request = $this->WeChat->getService( 'access_token' )->appendToRequest( $request, $options );

                return $handler( $request, $options );
            };
        };
    }

    /**
     * 请求重试中间件
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry( function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            if ( $retries < $this->config[ 'http' ][ 'retries' ] && $response && $body = $response->getBody() ) {
                $response = json_decode( $body, true );

                if ( ! empty( $response[ 'errcode' ] ) && in_array( abs( $response[ 'errcode' ] ), [ 40001, 40014, 42001 ], true ) ) {
                    $this->WeChat->getService( 'access_token' )->refresh();

                    Log::error( '微信调用时刷新了 access_token' );

                    return true;
                }
            }

            return false;
        }, function () {
            return $this->config[ 'http' ][ 'retry_delay' ];
        } );
    }

    /**
     * 获取 GuzzleHttp\Client 实例
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if ( ! ( $this->httpClient instanceof Client ) ) {
            $this->httpClient = $this->WeChat->getService( 'http_client' ) ? : new Client();
        }

        return $this->httpClient;
    }

    /**
     * 设置 guzzle 默认配置
     *
     * @param array $defaults
     */
    public static function setDefaultOptions( $defaults = [] )
    {
        self::$defaults = $defaults;
    }

    /**
     * 获取 guzzle 默认配置
     *
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$defaults;
    }

    /**
     * 设置 GuzzleHttp\Client.
     *
     * @param \GuzzleHttp\ClientInterface $httpClient
     *
     * @return $this
     */
    public function setHttpClient( ClientInterface $httpClient )
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * 添加中间件
     *
     * @param callable    $middleware
     * @param null|string $name
     *
     * @return $this
     */
    public function pushMiddleware( callable $middleware, $name = null )
    {
        if ( ! is_null( $name ) ) {
            $this->middlewares[ $name ] = $middleware;
        } else {
            array_push( $this->middlewares, $middleware );
        }

        return $this;
    }

    /**
     * 获取中间件
     *
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @param \GuzzleHttp\HandlerStack $handlerStack
     *
     * @return $this
     */
    public function setHandlerStack( HandlerStack $handlerStack )
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    /**
     * Build a handler stack.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack()
    {
        if ( $this->handlerStack ) {
            return $this->handlerStack;
        }

        $this->handlerStack = HandlerStack::create();

        foreach ( $this->middlewares as $name => $middleware ) {
            $this->handlerStack->push( $middleware, $name );
        }

        return $this->handlerStack;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function fixJsonIssue( array $options )
    {
        if ( isset( $options[ 'json' ] ) && is_array( $options[ 'json' ] ) ) {
            $options[ 'headers' ] = array_merge( empty( $options[ 'headers' ] ) ? [] : $options[ 'headers' ], [ 'Content-Type' => 'application/json' ] );

            if ( empty( $options[ 'json' ] ) ) {
                $options[ 'body' ] = \GuzzleHttp\json_encode( $options[ 'json' ], JSON_FORCE_OBJECT );
            } else {
                $options[ 'body' ] = \GuzzleHttp\json_encode( $options[ 'json' ], JSON_UNESCAPED_UNICODE );
            }

            unset( $options[ 'json' ] );
        }

        return $options;
    }

    protected function castResponseToType( ResponseInterface $response, $type = 'array' )
    {
        switch ( $type ) {
            case 'array':
                return $this->toArray( $response );
            case 'object':
                return $this->toObject( $response );
            default:
                throw new \InvalidArgumentException( '未设置返回类型' );
        }
    }

    protected function toArray( ResponseInterface $response )
    {
        $content = $response->getBody()->getContents();

        if ( false !== stripos( $response->getHeaderLine( 'Content-Type' ), 'xml' ) || 0 === stripos( $content, '<xml' ) ) {
            return XML::parse( $content );
        }

        $array = json_decode( $content, true );

        if ( JSON_ERROR_NONE === json_last_error() ) {
            return (array)$array;
        }

        return [];
    }

    protected function toObject( ResponseInterface $response )
    {
        return (object)$this->toArray( $response );
    }
}