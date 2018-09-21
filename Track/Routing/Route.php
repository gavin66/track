<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 11:27
 */

namespace Track\Routing;


use Prophecy\Doubler\Generator\TypeHintReference;
use Track\Container\ContainerContract as Container;
use Track\Http\Request;
use Track\Routing\Matching\HostValidator;
use Track\Routing\Matching\MethodValidator;
use Track\Routing\Matching\UriValidator;
use Track\Support\Arr;
use Track\Support\Str;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionFunctionAbstract;

class Route
{
    /**
     * 依赖容器
     *
     * @var Container
     */
    protected $container;

    /**
     * 路由的 uri
     *
     * @var string
     */
    public $uri;

    /**
     * HTTP 的动作(如 get,post 等)
     *
     * @var array
     */
    public $methods;

    /**
     * 路由的处理方法
     * $action = ['uses'=>'HelloController@index','middleware'=['auth']]
     *
     * @var array
     */
    public $action;

    /**
     * 路由导向的控制器实例
     *
     * @var \Track\Routing\Controller
     */
    public $controller;

    /**
     * 路由器
     *
     * @var Router
     */
    protected $router;

    /**
     * 路由验证器
     *
     * @var array
     */
    public static $validators;

    /**
     * 当前路由的中间件与路由到的控制器的中间件
     *
     * @var array|null
     */
    public $middleware;

    /**
     * 编译路由,正则
     *
     * @var \Symfony\Component\Routing\CompiledRoute
     */
    public $compiled;

    /**
     * The array of matched parameters.
     *
     * @var array
     */
    public $parameters;

    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    public $parameterNames;

    /**
     * The default values for the route.
     *
     * @var array
     */
    public $defaults = [];

    /**
     * 创建一个路由
     *
     * @param  array|string   $methods
     * @param  string         $uri
     * @param  \Closure|array $action
     *
     * @return void
     */
    public function __construct( $methods, $uri, $action )
    {
        $this->uri     = $uri;
        $this->methods = (array)$methods;
        $this->action  = $this->parseAction( $action );

        if ( in_array( 'GET', $this->methods ) && ! in_array( 'HEAD', $this->methods ) ) {
            $this->methods[] = 'HEAD';
        }
    }

    /**
     * Parse the route action into a standard array.
     *
     * @param  callable|array|null $action
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    protected function parseAction( $action )
    {
        return $this->parse( $this->uri, $action );
    }

    /**
     * 运行路由对应的控制器或闭包,返回响应
     *
     * @return mixed|string
     */
    public function run()
    {
        if ( $this->isControllerAction() ) {
            return $this->runController();
        }

        return $this->runCallable();
    }

    /**
     * 执行控制器的方法
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function runController()
    {
        $reflector = new ReflectionMethod( $controller = $this->getController(), $method = $this->getControllerMethod() );

        $parameters = $this->resolveClassMethodDependencies( $this->parametersWithoutNulls(), $reflector );

        return $controller->{$method}( ...array_values( $parameters ) );
    }

    /**
     * 执行路由闭包
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function runCallable()
    {
        $callable = $this->action[ 'uses' ];

        return $callable( ...array_values( $this->resolveClassMethodDependencies( $this->parametersWithoutNulls(), new ReflectionFunction( $callable ) ) ) );
    }

    /**
     * 检测是否是控制器,因为有可能是闭包
     *
     * @return bool
     */
    protected function isControllerAction()
    {
        return is_string( $this->action[ 'uses' ] );
    }

    /**
     * @param array                      $parameters
     * @param ReflectionFunctionAbstract $reflector
     *
     * @return array
     */
    protected function resolveClassMethodDependencies( array $parameters, ReflectionFunctionAbstract $reflector )
    {
        $instanceCount = 0;
        $values        = array_values( $parameters );

        foreach ( $reflector->getParameters() as $key => $parameter ) {
            if ( $class = $parameter->getClass() ) {
                if ( is_null( Arr::first( $parameters, function ( $value ) use ( $class ) {
                    return $value instanceof $class->name;
                } ) ) ) {
                    $instance = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : $this->container->make( $class->name );
                }
            }
            if ( isset( $instance ) && ! is_null( $instance ) ) {
                $instanceCount++;
                array_splice( $parameters, $key, 0, [ $instance ] );
            } elseif ( ! isset( $values[ $key - $instanceCount ] ) && $parameter->isDefaultValueAvailable() ) {
                array_splice( $parameters, $key, 0, $parameter->getDefaultValue() );
            }
        }

        return $parameters;
    }

    /**
     * 获取控制器实例
     *
     * @return \Track\Routing\Controller
     */
    public function getController()
    {
        if ( ! $this->controller ) {
            $class = $this->parseControllerCallback()[ 0 ];

            $this->controller = $this->container->make( $class );
        }

        return $this->controller;
    }

    /**
     * 获取控制器要执行的方法
     *
     * @return string
     */
    protected function getControllerMethod()
    {
        return $this->parseControllerCallback()[ 1 ];
    }

    /**
     *
     * 解析出控制器 控制器的类与方法
     *
     * @return array
     */
    protected function parseControllerCallback()
    {
        return Str::parseCallback( $this->action[ 'uses' ] );
    }

    /**
     * Get the URI associated with the route.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return isset( $this->action[ 'domain' ] )
            ? str_replace( [ 'http://', 'https://' ], '', $this->action[ 'domain' ] ) : null;
    }

    /**
     * 路由方法
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * 对请求进行路由验证
     *
     * @param Request $request
     *
     * @return bool
     */
    public function matches( Request $request )
    {
        $this->compileRoute();

        foreach ( $this->getValidators() as $validator ) {
            if ( ! $validator->matches( $this, $request ) ) {
                return false;
            }
        }

        return true;
    }

    public function bind( Request $request )
    {
        $this->compileRoute();

        $this->parameters = ( new RouteParameterBinder( $this ) )->parameters( $request );

        return $this;
    }

    /**
     * Get all of the parameter names for the route.
     *
     * @return array
     */
    public function parameterNames()
    {
        if ( isset( $this->parameterNames ) ) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParameterNames();
    }

    public function parametersWithoutNulls()
    {
        return array_filter( $this->parameters, function ( $p ) {
            return ! is_null( $p );
        } );
    }

    /**
     * Get the parameter names for the route.
     *
     * @return array
     */
    protected function compileParameterNames()
    {
        preg_match_all( '/\{(.*?)\}/', $this->getDomain() . $this->uri, $matches );

        return array_map( function ( $m ) {
            return trim( $m, '?' );
        }, $matches[ 1 ] );
    }

    /**
     * 编译路由
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    protected function compileRoute()
    {
        if ( ! $this->compiled ) {
            $this->compiled = ( new RouteCompiler( $this ) )->compile();
        }

        return $this->compiled;
    }

    /**
     * 获取路由验证器
     *
     * @return array
     */
    public static function getValidators()
    {
        if ( isset( static::$validators ) ) {
            return static::$validators;
        }

        return static::$validators = [ new UriValidator(), new MethodValidator(), new HostValidator() ];
    }

    /**
     * 设置路由器实例
     *
     * @param  Router $router
     *
     * @return $this
     */
    public function setRouter( Router $router )
    {
        $this->router = $router;

        return $this;
    }

    /**
     * 设置容器实例
     *
     * @param  Container $container
     *
     * @return $this
     */
    public function setContainer( Container $container )
    {
        $this->container = $container;

        return $this;
    }

    /**
     * 设置当前路由的中间件
     *
     * @param null $middleware
     *
     * @return $this|array
     */
    public function middleware( $middleware = null )
    {
        if ( is_null( $middleware ) ) {
            return (array)( Arr::get( $this->action, 'middleware', [] ) );
        }

        if ( is_string( $middleware ) ) {
            $middleware = func_get_args();
        }

        $this->action[ 'middleware' ] = array_merge( Arr::get( $this->action, 'middleware', [] ), $middleware );

        return $this;
    }

    /**
     * 当前控制器的中间件
     *
     * @return array
     */
    public function controllerMiddleware()
    {
        if ( ! $this->isControllerAction() )
            return [];

        $controller = $this->getController();
        $method     = $this->getControllerMethod();

        if ( ! method_exists( $controller, 'getMiddleware' ) ) {
            return [];
        }

        return collect( $controller->getMiddleware() )->reject( function ( $data ) use ( $method ) {
            return ( isset( $data[ 'options' ][ 'only' ] ) && ! in_array( $method, (array)$data[ 'options' ][ 'only' ] ) ) ||
                ( ! empty( $data[ 'options' ][ 'except' ] ) && in_array( $method, (array)$data[ 'options' ][ 'except' ] ) );
        } )->pluck( 'middleware' )->all();
    }

    /**
     * 当前路由与控制器的中间件
     *
     * @return array
     */
    public function gatherMiddleware()
    {
        if ( ! is_null( $this->middleware ) ) {
            return $this->middleware;
        }

        $this->middleware = [];

        return $this->middleware = array_unique( array_merge(
            $this->middleware(), $this->controllerMiddleware()
        ), SORT_REGULAR );
    }

    /**
     * 解析定义路由的 action
     *
     * @param $uri
     * @param $action
     *
     * @return array
     */
    private function parse( $uri, $action )
    {
        if ( is_null( $action ) ) {
            return $this->missingAction( $uri );
        }

        if ( is_callable( $action ) ) {
            return [ 'uses' => $action ];
        }

        if ( is_string( $action[ 'uses' ] ) && ! Str::contains( $action[ 'uses' ], '@' ) ) {
            $action[ 'uses' ] = $this->makeInvokable( $action[ 'uses' ] );
        }

        return $action;
    }

    /**
     * 路由解析时缺失 action
     *
     * @param $uri
     *
     * @return array
     */
    private function missingAction( $uri )
    {
        return [ 'uses' => function () use ( $uri ) {
            throw new \LogicException( "路由 [{$uri}] 未设置处理方法" );
        } ];
    }

    /**
     * 路由定义时未设置 method,但是类中有 __invoke 魔术方法
     *
     * @param $action
     *
     * @return string
     */
    private function makeInvokable( $action )
    {
        if ( ! method_exists( $action, '__invoke' ) ) {
            throw new \UnexpectedValueException( "无效的路由处理: [{$action}]" );
        }

        return $action . '@__invoke';
    }

    /**
     * 获取编译的路由
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function getCompiled()
    {
        return $this->compiled;
    }
}