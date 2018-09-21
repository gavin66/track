<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/19
 * Time: 18:00
 */

namespace Track\Http;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Track\Http\Concerns\RequestWithContentTypes;
use Track\Http\Concerns\RequestWithInput;
use Track\Routing\Route;
use Track\Session\Store;

class Request extends SymfonyRequest
{
    use RequestWithContentTypes,
        RequestWithInput;

    /**
     * 当前请求使用的路由
     *
     * @var Route
     */
    protected $route;

    /**
     * 解码出的 json 数据
     *
     * @var \Symfony\Component\HttpFoundation\ParameterBag|null
     */
    protected $json;

    /**
     * 生成 request 实例
     *
     * @return Request
     */
    public static function instance()
    {
        static::enableHttpMethodParameterOverride();

        $request = SymfonyRequest::createFromGlobals();

        $content = $request->content;

        $request = ( new static )->duplicate(
            $request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all()
        );

        $request->content = $content;

        $request->request = $request->getInputSource();

        return $request;
    }

    /**
     * 获取请求 method
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }

    /**
     * 是否是 ajax 请求
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * 是否是 pjax 请求
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->headers->get( 'X-PJAX' ) == true;
    }

    /**
     * 获取请求的 json 数据
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag|mixed
     */
    public function json( $key = null, $default = null )
    {
        if ( ! isset( $this->json ) ) {
            $this->json = new ParameterBag( (array)json_decode( $this->getContent(), true ) );
        }

        if ( is_null( $key ) ) {
            return $this->json;
        }

        return data_get( $this->json->all(), $key, $default );
    }

    /**
     * 获取当前请求的路径
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim( $this->getPathInfo(), '/' );

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * 获取当前请求 session
     *
     * @return Store
     */
    public function session()
    {
        if ( ! $this->hasSession() ) {
            throw new \RuntimeException( '当前请求未设置 session' );
        }

        return $this->session;
    }

    /**
     * 设置当前请求 session
     *
     * @param mixed $session
     */
    public function setTrackSession( $session )
    {
        $this->session = $session;
    }

    /**
     * 获取请求路由
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * 设置请求的路由
     *
     * @param Route $route
     */
    public function setRoute( Route $route )
    {
        $this->route = $route;
    }

    /**
     * 解析请求,获取相对应的数据源
     *
     * @return ParameterBag
     */
    protected function getInputSource()
    {
        if ( $this->isJson() ) {
            return $this->json();
        }

        return $this->getMethod() == 'GET' ? $this->query : $this->request;
    }
}