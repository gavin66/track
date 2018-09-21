<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/15
 * Time: 14:14
 */

namespace Track\WeChat\Foundation;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;

trait RequestTrait
{
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
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if ( ! ( $this->httpClient instanceof ClientInterface ) ) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
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
     * 创建请求
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return \Psr\Http\Message\ResponseInterface|array|object|string
     * @throws
     */
    public function request( $url, $method = 'GET', $options = [] )
    {
        $method = strtoupper( $method );

        $options = array_merge( self::$defaults, $options, [ 'handler' => $this->getHandlerStack() ] );

        $options = $this->fixJsonIssue( $options );

        $response = $this->getHttpClient()->request( $method, $url, $options );

        // 初始化位置
        $response->getBody()->rewind();

        return $response;
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

}