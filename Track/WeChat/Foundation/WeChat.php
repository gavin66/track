<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/16
 * Time: 10:45
 */

namespace Track\WeChat\Foundation;


use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Track\Container\ContainerContract;
use Track\Facades\Log;

class WeChat
{
    use RequestTrait {
        request as toRequest;
    }
    use ResponseTrait;

    /**
     * 服务容器
     *
     * @var ContainerContract
     */
    protected $container = null;

    /**
     * 服务前缀
     *
     * @var string
     */
    protected $servicePrefix = 'WeChat';

    /**
     * 本服务使用的依赖服务
     *
     * @var array
     */
    protected $providers = [];

    /**
     * 默认的配置项
     *
     * @var array
     */
    protected $config = [
        'http' => [
            'timeout'     => 5.0,
            'retries'     => 2, // 错误重试次数
            'retry_delay' => 500,
            'base_uri'    => 'https://api.weixin.qq.com/',
        ],
    ];

    public function __construct( ContainerContract $container, array $config = [] )
    {
        $this->container = $container;

        $this->config = array_merge( $this->config, $config );

        $this->registerProviders( $this->getProviders() );
    }

    /**
     * 发送请求
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     * @param bool   $raw
     *
     * @return \Psr\Http\Message\ResponseInterface|array|object|string
     *
     */
    public function request( $url, $method = 'GET', array $options = [], $raw = false )
    {
        if ( empty( $this->middlewares ) ) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->toRequest( $url, $method, $options );

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
                $request = $this->getService( 'access_token' )->appendToRequest( $request, $options );

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
                    $this->getService( 'access_token' )->refresh();

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
     * 注册服务
     *
     * @param $providers
     */
    public function registerProviders( $providers )
    {
        foreach ( $providers as $provider ) {
            $provider( $this->container );
        }
    }

    /**
     * 获取服务提供者
     *
     * @return array
     */
    public function getProviders()
    {
        return array_merge( [
            function ( ContainerContract $container ) {
                $container[ $this->getServiceName( 'http_client' ) ] = function () {
                    return new GuzzleClient( $this->config[ 'http' ] );
                };
            },
        ], $this->providers );
    }

    /**
     * 获取 GuzzleHttp\Client 实例
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if ( ! ( $this->httpClient instanceof Client ) ) {
            $this->httpClient = $this->getService( 'http_client' ) ? : new Client();
        }

        return $this->httpClient;
    }

    /**
     * @param array $providers
     */
    public function setProviders( $providers )
    {
        $this->providers = $providers;
    }

    /**
     * 合并自定义服务提供者
     *
     * @param $providers
     */
    public function mergeProviders( $providers )
    {
        $this->providers = array_merge( $this->providers, $providers );
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig( $config )
    {
        $this->config = $config;
    }

    /**
     * 获取服务名
     *
     * @param $name
     *
     * @return string
     */
    public function getServiceName( $name )
    {
        return $this->servicePrefix . '-' . $name;
    }

    /**
     * 获取服务
     *
     * @param $name
     *
     * @return mixed
     */
    public function getService( $name )
    {
        return $this->container->make( $this->getServiceName( $name ) );
    }

    /**
     * 魔术方法,获取服务
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get( $name )
    {
        return $this->getService( $name );
    }

}