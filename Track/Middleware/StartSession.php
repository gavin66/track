<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/22
 * Time: 12:06
 */

namespace Track\Middleware;


use Symfony\Component\HttpFoundation\Cookie;
use Track\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Track\Session\SessionContract as Session;
use Track\Session\SessionManager;
use Track\Support\Arr;

class StartSession implements MiddlewareContract
{

    /**
     * @var SessionManager
     */
    protected $manager;

    public function __construct( SessionManager $manager )
    {
        $this->manager = $manager;
    }

    /**
     * @param Request  $request
     * @param \Closure $next
     *
     * @return Response
     * @throws \Exception
     */
    public function handle( Request $request, \Closure $next )
    {
        $request->setTrackSession( $session = $this->startSession( $request ) );

        $this->collectGarbage( $session );

        $response = $next( $request );

        $this->addCookieToResponse( $response, $session );

        $session->save();

        return $response;
    }

    /**
     * 启动 session
     *
     * @param Request $request
     *
     * @return Session
     */
    protected function startSession( Request $request )
    {
        $session = $this->getSession( $request );

        $session->start();

        return $session;
    }

    /**
     * 获取 session
     *
     * @param Request $request
     *
     * @return Session
     */
    public function getSession( Request $request )
    {
        $session = $this->manager->driver();

        $session->setId( $request->cookies->get( $session->getName() ) );

        return $session;
    }

    /**
     * 默认 2/100 概率启动 session 回收
     * 如 redis 的缓存没有回收机制,因为它们有过期时效
     *
     * @param Session $session
     *
     * @throws \Exception
     */
    protected function collectGarbage( Session $session )
    {
        $config = $this->manager->getSessionConfig();

        if ( rand( 1, $config[ 'lottery' ][ 1 ] ) <= $config[ 'lottery' ][ 0 ] ) {
            $session->getHandler()->gc( Arr::get( $config, 'lifetime', 0 ) * 60 );
        }
    }

    /**
     * 响应中添加 cookie
     *
     * @param Response $response
     * @param Session  $session
     */
    protected function addCookieToResponse( Response $response, Session $session )
    {
        $config = $this->manager->getSessionConfig();

        $response->headers->setCookie(
            new Cookie(
                $session->getName(),
                $session->getId(),
                $this->getCookieExpireDate(),
                $config[ 'path' ],
                $config[ 'domain' ],
                $config[ 'secure' ],
                $config[ 'http_only' ],
                false,
                $config[ 'same_site' ]
            )
        );
    }

    /**
     * 获取 cookie 过期时间
     *
     * @return int
     */
    protected function getCookieExpireDate()
    {
        $config = $this->manager->getSessionConfig();

        return $config[ 'expire_on_close' ] ? 0 : time() + Arr::get( $config, 'lifetime', 0 ) * 60;
    }
}