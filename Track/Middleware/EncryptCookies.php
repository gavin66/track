<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/7/19
 * Time: 15:19
 */

namespace Track\Middleware;


use Symfony\Component\HttpFoundation\Cookie;
use Track\Encryption\DecryptException;
use Track\Encryption\EncrypterContract;
use Track\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EncryptCookies implements MiddlewareContract
{
    /**
     * @var EncrypterContract
     */
    protected $encrypter;

    /**
     * 排除的 cookie 不被加密
     *
     * @var array
     */
    protected $except = [];


    public function __construct( EncrypterContract $encrypter )
    {
        $this->encrypter = $encrypter;
    }

    public function handle( Request $request, \Closure $next )
    {
        return $this->encrypt( $next( $this->decrypt( $request ) ) );
    }

    /**
     * http 请求时解密
     *
     * @param Request $request
     *
     * @return Request
     */
    protected function decrypt( Request $request )
    {
        foreach ( $request->cookies as $key => $cookie ) {
            if ( $this->isDisabled( $key ) ) {
                continue;
            }

            try {
                $request->cookies->set( $key, $this->encrypter->decrypt( $cookie ) );
            } catch ( DecryptException $exception ) {
                $request->cookies->set( $key, null );
            }
        }

        return $request;
    }

    /**
     * http 响应时加密
     *
     * @param Response $response
     *
     * @return Response
     */
    protected function encrypt( Response $response )
    {
        foreach ( $response->headers->getCookies() as $cookie ) {
            if ( $this->isDisabled( $cookie->getName() ) ) {
                continue;
            }

            $response->headers->setCookie( $this->duplicate(
                $cookie, $this->encrypter->encrypt( $cookie->getValue() )
            ) );
        }

        return $response;
    }

    /**
     * 重新设置 cookie
     *
     * @param Cookie $cookie
     * @param        $value
     *
     * @return Cookie
     */
    protected function duplicate( Cookie $cookie, $value )
    {
        return new Cookie(
            $cookie->getName(), $value, $cookie->getExpiresTime(),
            $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(),
            $cookie->isHttpOnly(), $cookie->isRaw(), $cookie->getSameSite()
        );
    }

    /**
     * 确定是否排除 cookie
     *
     * @param $name
     *
     * @return bool
     */
    protected function isDisabled( $name )
    {
        return in_array( $name, $this->except );
    }
}