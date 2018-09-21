<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/8
 * Time: 15:53
 */

namespace Track\Http;

use Symfony\Component\HttpFoundation\Response;

/**
 * 跨域支持服务
 * 跨域说明参考
 * https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 * https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Access_control_CORS
 *
 * @package Track\Http
 */
class CorsService
{
    private $options;

    public function __construct( array $options = [] )
    {
        $this->options = $this->normalizeOptions( $options );
    }

    private function normalizeOptions( array $options = [] )
    {
        $options += [
            'allowedOrigins'         => [],
            'allowedOriginsPatterns' => [],
            'supportsCredentials'    => false,
            'allowedHeaders'         => [],
            'exposedHeaders'         => [],
            'allowedMethods'         => [],
            'maxAge'                 => 0,
        ];

        // normalize array('*') to true
        if ( in_array( '*', $options[ 'allowedOrigins' ] ) ) {
            $options[ 'allowedOrigins' ] = true;
        }
        if ( in_array( '*', $options[ 'allowedHeaders' ] ) ) {
            $options[ 'allowedHeaders' ] = true;
        } else {
            $options[ 'allowedHeaders' ] = array_map( 'strtolower', $options[ 'allowedHeaders' ] );
        }

        if ( in_array( '*', $options[ 'allowedMethods' ] ) ) {
            $options[ 'allowedMethods' ] = true;
        } else {
            $options[ 'allowedMethods' ] = array_map( 'strtoupper', $options[ 'allowedMethods' ] );
        }

        return $options;
    }

    public function isActualRequestAllowed( Request $request )
    {
        return $this->checkOrigin( $request );
    }

    /**
     * 确定是否是跨域请求
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isCorsRequest( Request $request )
    {
        return $request->headers->has( 'Origin' ) && ! $this->isSameHost( $request );
    }

    /**
     * 确定是否 预检请求(preflight)
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isPreflightRequest( Request $request )
    {
        return $this->isCorsRequest( $request )
            && $request->getMethod() === 'OPTIONS'
            && $request->headers->has( 'Access-Control-Request-Method' );
    }

    public function addActualRequestHeaders( Response $response, Request $request )
    {
        if ( ! $this->checkOrigin( $request ) ) {
            return $response;
        }

        $response->headers->set( 'Access-Control-Allow-Origin', $request->headers->get( 'Origin' ) );

        if ( ! $response->headers->has( 'Vary' ) ) {
            $response->headers->set( 'Vary', 'Origin' );
        } else {
            $response->headers->set( 'Vary', $response->headers->get( 'Vary' ) . ', Origin' );
        }

        if ( $this->options[ 'supportsCredentials' ] ) {
            $response->headers->set( 'Access-Control-Allow-Credentials', 'true' );
        }

        if ( $this->options[ 'exposedHeaders' ] ) {
            $response->headers->set( 'Access-Control-Expose-Headers', implode( ', ', $this->options[ 'exposedHeaders' ] ) );
        }

        return $response;
    }

    /**
     * 对于一些可能对服务器数据有影响的请求,如 PUT DELETE 和搭配某些 MIME 类型的 POST 方法,
     * 浏览器必须先发送一个 "预检请求(preflight)",来确认服务器是否允许该请求,允许的话再真正发送相应的请求.
     */
    public function handlePreflightRequest( Request $request )
    {
        if ( true !== $check = $this->checkPreflightRequestConditions( $request ) ) {
            return $check;
        }

        return $this->buildPreflightCheckResponse( $request );
    }

    private function buildPreflightCheckResponse( Request $request )
    {
        $response = new Response();

        if ( $this->options[ 'supportsCredentials' ] ) {
            $response->headers->set( 'Access-Control-Allow-Credentials', 'true' );
        }

        $response->headers->set( 'Access-Control-Allow-Origin', $request->headers->get( 'Origin' ) );

        if ( $this->options[ 'maxAge' ] ) {
            $response->headers->set( 'Access-Control-Max-Age', $this->options[ 'maxAge' ] );
        }

        $allowMethods = $this->options[ 'allowedMethods' ] === true
            ? strtoupper( $request->headers->get( 'Access-Control-Request-Method' ) )
            : implode( ', ', $this->options[ 'allowedMethods' ] );
        $response->headers->set( 'Access-Control-Allow-Methods', $allowMethods );

        $allowHeaders = $this->options[ 'allowedHeaders' ] === true
            ? strtoupper( $request->headers->get( 'Access-Control-Request-Headers' ) )
            : implode( ', ', $this->options[ 'allowedHeaders' ] );
        $response->headers->set( 'Access-Control-Allow-Headers', $allowHeaders );

        return $response;
    }

    private function checkPreflightRequestConditions( Request $request )
    {
        if ( ! $this->checkOrigin( $request ) ) {
            return $this->createBadRequestResponse( 403, '不支持的 Origin' );
        }

        if ( ! $this->checkMethod( $request ) ) {
            return $this->createBadRequestResponse( 405, '不支持的 method' );
        }

        $requestHeaders = [];
        // if allowedHeaders has been set to true ('*' allow all flag) just skip this check
        if ( $this->options[ 'allowedHeaders' ] !== true && $request->headers->has( 'Access-Control-Request-Headers' ) ) {
            $headers        = strtolower( $request->headers->get( 'Access-Control-Request-Headers' ) );
            $requestHeaders = array_filter( explode( ',', $headers ) );

            foreach ( $requestHeaders as $header ) {
                if ( ! in_array( trim( $header ), $this->options[ 'allowedHeaders' ] ) ) {
                    return $this->createBadRequestResponse( 403, 'Header not allowed' );
                }
            }
        }

        return true;
    }

    private function createBadRequestResponse( $code, $reason = '' )
    {
        return new Response( $reason, $code );
    }

    private function isSameHost( Request $request )
    {
        return $request->headers->get( 'Origin' ) === $request->getSchemeAndHttpHost();
    }

    private function checkOrigin( Request $request )
    {
        if ( $this->options[ 'allowedOrigins' ] === true ) {
            // allow all '*' flag
            return true;
        }
        $origin = $request->headers->get( 'Origin' );

        if ( in_array( $origin, $this->options[ 'allowedOrigins' ] ) ) {
            return true;
        }

        foreach ( $this->options[ 'allowedOriginsPatterns' ] as $pattern ) {
            if ( preg_match( $pattern, $origin ) ) {
                return true;
            }
        }

        return false;
    }

    private function checkMethod( Request $request )
    {
        if ( $this->options[ 'allowedMethods' ] === true ) {
            // allow all '*' flag
            return true;
        }

        $requestMethod = strtoupper( $request->headers->get( 'Access-Control-Request-Method' ) );

        return in_array( $requestMethod, $this->options[ 'allowedMethods' ] );
    }
}