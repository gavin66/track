<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/7
 * Time: 17:21
 */

namespace Track\Middleware;


use Track\Http\CorsService;
use Track\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EnableCrossRequest
 * 支持跨域
 *
 * @package Track\Middleware
 */
class EnableCrossRequest implements MiddlewareContract
{

    /**
     * 跨域服务
     *
     * @var CorsService
     */
    private $cors;

    public function handle( Request $request, \Closure $next )
    {
        // 创建跨域服务
        app()->singleton( CorsService::class, $this->cors = new CorsService( $this->config() ) );

        if ( ! $this->cors->isCorsRequest( $request ) ) {
            return $next( $request );
        }

        if ( $this->cors->isPreflightRequest( $request ) ) {
            return $this->cors->handlePreflightRequest( $request );
        }

        if ( ! $this->cors->isActualRequestAllowed( $request ) ) {
            return new Response( "不允许跨域", 403 );
        }

        $response = $next( $request );

        return $this->addHeaders( $request, $response );
    }

    private function config()
    {
        return [
            'supportsCredentials'    => false,
            // 允许跨域的
            'allowedOrigins'         => [ '*' ],
            // 允许跨域的正则
            'allowedOriginsPatterns' => [],
            'allowedHeaders'         => [ '*' ],
            // 允许跨域的 method 例如: ['GET', 'POST', 'PUT',  'DELETE']
            'allowedMethods'         => [ '*' ],
            'exposedHeaders'         => [],
            'maxAge'                 => 0,
        ];
    }

    /**
     * 添加跨域请求
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    protected function addHeaders( Request $request, Response $response )
    {
        // Prevent double checking
        if ( ! $response->headers->has( 'Access-Control-Allow-Origin' ) ) {
            $response = $this->cors->addActualRequestHeaders( $response, $request );
        }

        return $response;
    }

}