<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 11:27
 */

namespace Track\Routing;


use Track\Container\ContainerContract as Container;
use Track\Http\Request;
use Track\Http\Response;
use Track\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Router implements RouterContract
{
    /**
     * 依赖容器
     *
     * @var Container
     */
    protected $container;

    /**
     * 路由集合
     *
     * @var RouteCollection
     */
    protected $routes;

    /**
     * 当前请求匹配的路由
     *
     * @var Route
     */
    protected $current;

    /**
     * 当前的请求
     *
     * @var Request
     */
    protected $currentRequest;

    /**
     * 路由器支持的所有动作
     *
     * @var array
     */
    public static $verbs = [ 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS' ];

    /**
     * 创建路由器
     *
     * @param Container $container
     *
     * @return void
     */
    public function __construct( Container $container = null )
    {
        $this->routes    = new RouteCollection;
        $this->container = $container;
    }

    /**
     * 注册 get 路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function get( $uri, $action )
    {
        return $this->addRoute( [ 'GET', 'HEAD' ], $uri, $action );
    }

    /**
     * 注册 post 路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function post( $uri, $action )
    {
        return $this->addRoute( 'POST', $uri, $action );
    }

    /**
     * 注册 put 路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function put( $uri, $action )
    {
        return $this->addRoute( 'PUT', $uri, $action );
    }

    /**
     * 注册 delete 路由
     *
     * @param string          $uri
     * @param \Closure|string $action
     *
     * @return Route
     */
    public function delete( $uri, $action )
    {
        return $this->addRoute( 'DELETE', $uri, $action );
    }

    /**
     * todo 资源路由器未实现
     *
     * @param string $name
     * @param string $controller
     * @param array  $options
     *
     * @return mixed|void
     */
    public function resource( $name, $controller, array $options = [] )
    {

    }

    /**
     * 加载路由,闭包和加载 routes.php 文件
     *
     * @param $routes
     */
    public function loadRoutes( $routes )
    {
        if ( $routes instanceof \Closure ) {
            $routes( $this );
        } else {
            require $routes;
        }
    }

    /**
     * 添加路由
     *
     * @param  array|string               $methods
     * @param  string                     $uri
     * @param  \Closure|array|string|null $action
     *
     * @return Route
     */
    protected function addRoute( $methods, $uri, $action )
    {
        return $this->routes->add( $this->createRoute( $methods, $uri, $action ) );
    }

    /**
     * 创建路由实例
     *
     * @param  array|string $methods
     * @param  string       $uri
     * @param  mixed        $action
     *
     * @return Route
     */
    protected function createRoute( $methods, $uri, $action )
    {
        if ( $this->actionReferencesController( $action ) ) {
            $action = $this->convertToControllerAction( $action );
        }

        $route = $this->newRoute( $methods, $uri, $action );

        return $route;
    }

    /**
     * 请求发送到指定控制器的方法中
     *
     * @param  Request $request
     *
     * @return Response|JsonResponse
     */
    public function dispatch( Request $request )
    {
        $this->currentRequest = $request;

        return $this->runRoute( $request, $this->findRoute( $request ) );
    }

    /**
     * 运行路由返回响应
     *
     * @param  Route   $route
     * @param  Request $request
     *
     * @return mixed
     */
    protected function runRoute( Request $request, Route $route )
    {
        $request->setRoute( $route );

        return static::toResponse( $request, $this->runRouteWithMiddleware( $request, $route ) );
    }

    /**
     * 运行中间件返回响应
     *
     * @param Request $request
     * @param Route   $route
     *
     * @return Response
     */
    protected function runRouteWithMiddleware( Request $request, Route $route )
    {
        $pipeline = array_reduce(
            array_reverse( $route->gatherMiddleware() ),
            function ( $stack, $pipe ) {
                return function ( $request ) use ( $stack, $pipe ) {
                    if ( is_callable( $pipe ) ) {
                        return $pipe( $request, $stack );
                    } elseif ( ! is_object( $pipe ) ) {
                        $pipe = app( $pipe );
                    }

                    return $pipe->handle( $request, $stack );
                };
            },
            function () use ( $route ) {
                return call_user_func( function () use ( $route ) {
                    return $route->run();
                } );
            }
        );

        return $pipeline( $request );
    }

    /**
     * 寻找与请求匹配的路由
     *
     * @param  Request $request
     *
     * @return Route
     */
    protected function findRoute( $request )
    {
        $this->current = $route = $this->routes->match( $request );

        $this->container->instance( Route::class, $route );

        return $route;
    }

    /**
     * 确定路由参数中 action 是否是一个控制器
     *
     * @param  array $action
     *
     * @return bool
     */
    protected function actionReferencesController( $action )
    {
        if ( ! $action instanceof \Closure ) {
            return is_string( $action ) || ( isset( $action[ 'uses' ] ) && is_string( $action[ 'uses' ] ) );
        }

        return false;
    }

    /**
     *
     * @param  array|string $action
     *
     * @return array
     */
    protected function convertToControllerAction( $action )
    {
        if ( is_string( $action ) ) {
            $action = [ 'uses' => $action ];
        }

        return $action;
    }

    /**
     * 创建路由实例
     *
     * @param  array|string $methods
     * @param  string       $uri
     * @param  mixed        $action
     *
     * @return Route
     */
    protected function newRoute( $methods, $uri, $action )
    {
        return ( new Route( $methods, $uri, $action ) )->setRouter( $this )->setContainer( $this->container );
    }

    /**
     * 返回响应
     *
     * @param  Request $request
     * @param  mixed   $response
     *
     * @return Response|JsonResponse
     */
    public static function toResponse( $request, $response )
    {
        if ( ! $response instanceof SymfonyResponse ) {
            if ( is_array( $response ) || $response instanceof \ArrayObject || $response instanceof \JsonSerializable )
                $response = new JsonResponse( $response );
            else
                $response = new Response( $response );
        }

        return $response->prepare( $request );
    }

}