<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/10
 * Time: 12:06
 */

namespace Track;

use Track\Cache\CacheManager;
use Track\Cache\Factory;
use Track\Container\Container;
use Track\Container\ContainerContract;
use Track\Database\DatabaseManager;
use Track\Encryption\Encrypter;
use Track\Encryption\EncrypterContract;
use Track\Foundation\Exceptions\FatalThrowableError;
use Track\Foundation\HandleExceptions;
use Track\Foundation\LoadConfiguration;
use Track\Foundation\LoadEnvironmentVariables;
use Track\Foundation\RegisterFacades;
use Track\Foundation\ServiceProvider;
use Track\Http\Request;
use Track\Http\Response;
use Track\Log\Writer;
use Track\Middleware\EnableCrossRequest;
use Track\Middleware\EncryptCookies;
use Track\Middleware\StartSession;
use Track\Routing\Router;
use Track\Session\SessionManager;
use Track\Support\Arr;
use Track\Foundation\Exceptions\Handler as ExceptionHandler;

class Application extends Container
{
    /**
     * 项目的根目录
     *
     * @var string
     */
    protected $basePath;

    /**
     * 应用程序初始化是否已完成
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * 已经注册完成的服务提供者
     *
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * 应用启动时,加载基础类
     *
     * @var array
     */
    protected $bootstrappers = [
        LoadEnvironmentVariables::class, // 加载 .env 文件
        LoadConfiguration::class, // 加载 config 下所有配置文件
        HandleExceptions::class, // 处理未捕获的异常
        RegisterFacades::class, // 注册门面
    ];

    /**
     * 全局中间件(**顺序不能变动**)
     *
     * 每个请求在控制器运行前都会执行一遍中间件
     * 过滤请求,添加 header 等功能
     *
     * @var array
     */
    protected $middleware = [
        // cookie 加密
        EncryptCookies::class,
        // 跨域支持
        EnableCrossRequest::class,
        // session 支持
        StartSession::class,
    ];

    /**
     * 创建全局服务容器(非常重要)
     * 所有的服务与服务提供者都会在此容器中管理
     *
     * @param null $basePath
     */
    public function __construct( $basePath = null )
    {
        // 应用的各种路径
        if ( $basePath ) {
            $this->setBasePath( $basePath );
        }

        // 注册全局容器
        $this->registerBaseBindings();

        // 启动基础服务
        $this->bootstrap( $this->bootstrappers );

        // 注册基础服务提供者
        $this->registerBaseServiceProviders();

        // 注册核心服务别名
        $this->registerCoreServiceAliases();
    }

    /**
     * 设置基础路径
     *
     * @param  string $basePath
     *
     * @return $this
     */
    public function setBasePath( $basePath )
    {
        $this->basePath = rtrim( $basePath, '\/' );

        $this->instance( 'path.base', $this->basePath() );
        $this->instance( 'path.config', $this->configPath() );
        $this->instance( 'path.public', $this->publicPath() );
        $this->instance( 'path.database', $this->databasePath() );
        $this->instance( 'path.storage', $this->storagePath() );

        return $this;
    }

    /**
     * 项目根目录
     *
     * @param string $path
     *
     * @return string
     */
    public function basePath( $path = '' )
    {
        return $this->basePath . ( $path ? DIRECTORY_SEPARATOR . $path : $path );
    }

    /**
     * 应用配置文件路径
     *
     * @param string $path
     *
     * @return string
     */
    public function configPath( $path = '' )
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config' . ( $path ? DIRECTORY_SEPARATOR . $path : $path );
    }

    /**
     * 应用 public 入口目录
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * 数据库目录
     *
     * @param string $path
     *
     * @return string
     */
    public function databasePath( $path = '' )
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'database' . ( $path ? DIRECTORY_SEPARATOR . $path : $path );
    }

    /**
     * 应用生成的数据存储的路径
     *
     * @param string $path
     *
     * @return string
     */
    public function storagePath( $path = '' )
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'storage' . ( $path ? DIRECTORY_SEPARATOR . $path : $path );
    }

    /**
     * 注册全局容器(重要)
     */
    protected function registerBaseBindings()
    {
        static::$instance = $this;

        $this->instance( 'app', $this );
    }

    /**
     * 启动基础服务
     *
     * @param array $bootstrappers
     *
     * @throws
     */
    public function bootstrap( array $bootstrappers )
    {
        if ( $this->hasBeenBootstrapped === false ) {
            $this->hasBeenBootstrapped = true;

            foreach ( $bootstrappers as $bootstrapper ) {
                $this->make( $bootstrapper )->bootstrap( $this );
            }
        }
    }

    /**
     * 注册服务提供者
     */
    protected function registerBaseServiceProviders()
    {
        foreach ( $this[ 'config' ][ 'app.providers' ] as $provider ) {
            $this->register( new $provider( $this ) );
        }
    }

    /**
     * 注册核心服务的别名
     */
    protected function registerCoreServiceAliases()
    {
        foreach ( [
                      'app'       => [ Application::class, Container::class, ContainerContract::class ],
                      'db'        => [ DatabaseManager::class ],
                      'cache'     => [ CacheManager::class, Factory::class ],
                      'log'       => [ Writer::class ],
                      'request'   => [ Request::class ],
                      'router'    => [ Router::class ],
                      'session'   => [ SessionManager::class ],
                      'encrypter' => [ Encrypter::class, EncrypterContract::class ],
                  ] as $key => $aliases ) {
            foreach ( $aliases as $alias ) {
                $this->alias( $key, $alias );
            }
        }
    }

    /**
     * 注册服务提供者
     *
     * @param       $provider
     * @param bool  $force 强制加载,不论是否已加载
     *
     * @return bool
     */
    public function register( $provider, $force = false )
    {
        if ( $registered = $this->getProvider( $provider ) && ! $force ) {
            return $registered;
        }

        if ( method_exists( $provider, 'register' ) ) {
            $provider->register();
        }

        $this->serviceProviders[] = $provider;

        // 有的服务提供者注册后需要初始化一些配置
        if ( method_exists( $provider, 'boot' ) ) {
            // todo 未解析依赖
            return call_user_func_array( [ $provider, 'boot' ], [] );
        }

        return $provider;
    }

    /**
     * 获取已经注册的服务提供者实例,获取第一索引
     *
     * @param ServiceProvider|string $provider
     *
     * @return ServiceProvider|null
     */
    public function getProvider( $provider )
    {
        return isset( array_values( $this->getProviders( $provider ) )[ 0 ] ) ? array_values( $this->getProviders( $provider ) )[ 0 ] : null;
    }

    /**
     * 获取已经注册的服务提供者实例,一个数组
     *
     * @param $provider
     *
     * @return array
     */
    public function getProviders( $provider )
    {
        $name = is_string( $provider ) ? $provider : get_class( $provider );

        return Arr::where( $this->serviceProviders, function ( $value ) use ( $name ) {
            return $value instanceof $name;
        } );
    }

    /**
     * 获取全局容器实例
     *
     * @return Application
     */
    public static function getInstance()
    {
        if ( is_null( static::$instance ) ) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 处理 HTTP 请求,返回响应实例
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle( Request $request )
    {
        try {
            // 在返回响应前, 运行请求的前后中间件
            // 生成一个中间件与控制器执行的管道
            $pipeline = array_reduce(
            // 中间件数组
                array_reverse( $this->middleware ),
                // 管道闭包
                function ( $stack, $pipe ) {
                    return function ( $request ) use ( $stack, $pipe ) {
                        if ( is_callable( $pipe ) ) {
                            return $pipe( $request, $stack );
                        } elseif ( ! is_object( $pipe ) ) {
                            $pipe = $this->make( $pipe );
                        }

                        return $pipe->handle( $request, $stack );
                    };
                },
                // 管道执行的终点,就是返回响应
                function ( $request ) {
                    return call_user_func( function ( $request ) {
                        return $this[ 'router' ]->dispatch( $request );
                    }, $request );
                }
            );

            // 执行这个管道,返回响应
            $response = $pipeline( $request );
        } catch ( \Exception $exception ) {
            $this[ ExceptionHandler::class ]->report( $exception );

            $response = $this[ ExceptionHandler::class ]->render( $request, $exception );
        } catch ( \Throwable $throwable ) {
            $this[ ExceptionHandler::class ]->report( new FatalThrowableError( $throwable ) );

            $response = $this[ ExceptionHandler::class ]->render( $request, $throwable );
        }

        return $response;
    }

}