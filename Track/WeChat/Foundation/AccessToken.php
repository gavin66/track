<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/16
 * Time: 13:56
 */

namespace Track\WeChat\Foundation;


use Psr\Http\Message\RequestInterface;
use Track\Facades\Cache;

abstract class AccessToken implements AccessTokenContracts
{
    use RequestTrait;

    /**
     * 实例(如公众平台,开放平台,小程序等)
     *
     * @var \Track\WeChat\Foundation\WeChat
     */
    protected $WeChat;

    /**
     * 请求 token 时的 method
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * 请求 token 时的 path
     *
     * @var string
     */
    protected $path;

    /**
     * 请求附加 access_token 时的参数名
     *
     * @var string
     */
    protected $queryName;

    /**
     * 缓存的安全时间(不会等到真正微信 token 失效的时间,提前刷新)
     *
     * @var int
     */
    protected $safeSeconds = 500;

    /**
     * 从微信获取 token 接口的结果键名
     *
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * token 缓存时的前缀
     *
     * @var string
     */
    protected $cachePrefix = 'wechat.access_token';

    /**
     *  初始化
     *
     * @param \Track\WeChat\Foundation\WeChat $WeChat 哪一个平台使用
     */
    public function __construct( WeChat $WeChat )
    {
        $this->WeChat = $WeChat;
    }

    /**
     * 获取 access_token
     *
     * @param bool $refresh
     *
     * @return string
     */
    public function getToken( $refresh = false )
    {
        if ( ! $refresh && Cache::has( $cacheKey = $this->getCacheKey() ) ) {
            return Cache::get( $cacheKey );
        }

        $token = $this->requestToken( $this->getRequestData() );

        $this->setToken( $token[ $this->tokenKey ], empty( $token[ 'expires_in' ] ) ? $token[ 'expires_in' ] : 7200 );

        return $token[ $this->tokenKey ];
    }

    /**
     * 删除缓存中的 token 并重新获取
     *
     * @return string
     * @throws \Exception
     */
    public function refresh()
    {
        return $this->getToken( true );
    }

    /**
     * 获取 token
     *
     * @param array $data
     *
     * @return array
     */
    public function requestToken( array $data )
    {
        $response = $this->sendRequest( $data );
        $result   = json_decode( $response->getBody()->getContents(), true );

        if ( empty( $result[ $this->tokenKey ] ) ) {
            throw new \RuntimeException( '获取 access_token 失败: ' . json_encode( $result, JSON_UNESCAPED_UNICODE ), $response->getStatusCode() );
        }

        return $result;
    }

    /**
     * 发送获取 token 的请求
     *
     * @param array $data
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendRequest( $data )
    {
        $options = [
            ( 'GET' === $this->method ) ? 'query' : 'json' => $data,
        ];

        return $this->setHttpClient( $this->WeChat->getService( 'http_client' ) )->request( $this->getPath(), $this->method, $options );
    }

    /**
     * 设置 access_token 缓存
     *
     * @param string $token
     * @param int    $lifetime
     *
     * @return AccessTokenContracts
     */
    public function setToken( $token, $lifetime = 7200 )
    {
        Cache::set( $this->getCacheKey(), $token, $this->getMinutes( $lifetime - $this->safeSeconds ) );

        return $this;
    }

    /**
     * 获取 token 的缓存 key
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->cachePrefix . md5( json_encode( $this->getRequestData() ) );
    }

    /**
     * 获取 token 时的请求路径与参数
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getPath()
    {
        if ( empty( $this->path ) ) {
            throw new \InvalidArgumentException( '未设置请求 uri' );
        }

        return $this->path;
    }

    /**
     * 请求中附加 access_token 参数
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $requestOptions
     *
     * @return \Psr\Http\Message\RequestInterface
     * @throws \Exception
     */
    public function appendToRequest( RequestInterface $request, array $requestOptions = [] )
    {
        parse_str( $request->getUri()->getQuery(), $query );

        $query = http_build_query( array_merge( $this->getQuery(), $query ) );

        return $request->withUri( $request->getUri()->withQuery( $query ) );
    }

    /**
     * 附加请求的 access_token
     *
     * @return array
     */
    protected function getQuery()
    {
        return [ $this->queryName ? : $this->tokenKey => $this->getToken() ];
    }

    /**
     * 换算分钟
     *
     * @param $secs
     *
     * @return int
     */
    protected function getMinutes( $secs )
    {
        return $secs ? (int)( $secs / 60 ) : 0;
    }

    /**
     * 获取请求 token 时的附带参数
     *
     * @return array
     */
    abstract protected function getRequestData();
}