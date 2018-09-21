<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/25
 * Time: 17:09
 */

namespace Track\Container;

use ArrayAccess;
use Closure;
use ReflectionClass;
use LogicException;
use ReflectionParameter;

class Container implements ArrayAccess, ContainerContract
{
    /**
     * 全局的容器实例
     *
     * @var static
     */
    protected static $instance;

    /**
     * 容器中共享的实例(单例)
     *
     * @var array
     */
    protected $instances = [];

    /**
     * 抽象类型的别名(一个别名只能对应一个抽象)
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * 解析抽象类型时,需要的参数数组
     *
     * $bindings = ['db'=>['concrete'=> dbClosure','shared' => true],'cache'=>['concrete' => cacheClosure,'shared' =>
     * false]];
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * 已经解析过的实例类型
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * 解析实例时,传入的构造函数参数数组
     *
     * @var array
     */
    protected $with = [];

    /**
     * 注册一个单例
     *
     * @param      $abstract
     * @param null $concrete
     *
     * @return mixed|void
     */
    public function singleton( $abstract, $concrete = null )
    {
        $this->bind( $abstract, $concrete, true );
    }

    /**
     * 绑定抽象类型
     *
     * @param string  $abstract 抽象
     * @param Closure $concrete 实体闭包
     * @param bool    $shared   是否是单例
     */
    public function bind( $abstract, $concrete = null, $shared = false )
    {
        // 删除已经绑定的
        unset( $this->instances[ $abstract ], $this->aliases[ $abstract ] );

        // 如果实体未设置,那就把抽象类型设置为实体(待实例化)
        if ( is_null( $concrete ) ) {
            $concrete = $abstract;
        }

        // 如果给定的实体不是闭包,那就需要创建闭包
        if ( ! $concrete instanceof Closure ) {
            $concrete = function ( ContainerContract $container, $parameters = [] ) use ( $abstract, $concrete ) {
                if ( $abstract == $concrete ) {
                    return $container->build( $concrete );
                }

                return $container->make( $concrete, $parameters );
            };
        }

        $this->bindings[ $abstract ] = compact( 'concrete', 'shared' );

    }

    /**
     * 给定的抽象类型是否已经绑定到容器
     *
     * @param $abstract
     *
     * @return bool
     */
    public function bound( $abstract )
    {
        return isset( $this->bindings[ $abstract ] ) || isset( $this->instances[ $abstract ] ) || $this->isAlias( $abstract );
    }

    /**
     * 绑定一个实例
     *
     * @param $abstract
     * @param $instance
     *
     * @return mixed
     */
    public function instance( $abstract, $instance )
    {
        unset( $this->aliases[ $abstract ] );

        $this->instances[ $abstract ] = $instance;

        return $instance;
    }

    /**
     * 解析抽象
     *
     * @param       $abstract
     * @param array $parameters
     *
     * @return mixed|object
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function make( $abstract, array $parameters = [] )
    {
        return $this->resolve( $abstract, $parameters );
    }

    /**
     * 解析抽象
     *
     * @param       $abstract
     * @param array $parameters
     *
     * @return mixed|object
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resolve( $abstract, $parameters = [] )
    {
        $abstract = $this->getAlias( $abstract );

        // 返回已经存在的实例(单例)
        if ( isset( $this->instances[ $abstract ] ) ) {
            return $this->instances[ $abstract ];
        }

        // 构造函数参数
        $this->with[] = $parameters;

        // 获取抽象实体
        if ( isset( $this->bindings[ $abstract ] ) ) {
            $concrete = $this->bindings[ $abstract ][ 'concrete' ];
        } else {
            $concrete = $abstract;
        }

        // 开始解析具体的抽象实体
        if ( $concrete === $abstract || $concrete instanceof Closure ) {
            $object = $this->build( $concrete );
        } else {
//            $object = $this->make($concrete);
            throw new BindingResolutionException( "无法解析抽象 [$abstract] 的实现 [$concrete]" );
        }

        // 如果构建的是单例
        if ( isset( $this->instances[ $abstract ] ) || ( isset( $this->bindings[ $abstract ][ 'shared' ] ) && $this->bindings[ $abstract ][ 'shared' ] === true ) ) {
            $this->instances[ $abstract ] = $object;
        }

        $this->resolved[ $abstract ] = true;

        array_pop( $this->with );

        return $object;
    }

    /**
     * 是否已解析
     *
     * @param $abstract
     *
     * @return bool
     */
    public function resolved( $abstract )
    {
        if ( $this->isAlias( $abstract ) ) {
            $abstract = $this->getAlias( $abstract );
        }

        return isset( $this->resolved[ $abstract ] ) || isset( $this->instances[ $abstract ] );
    }

    /**
     * 实例化具体抽象类型的实例
     *
     * @param $concrete
     *
     * @return mixed|object
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function build( $concrete )
    {
        // 如果是闭包,就执行它并返回结果
        if ( $concrete instanceof Closure ) {
            return $concrete( $this );
        }

        // 如果是一个类名字符串,那就需要使用到反射
        $reflector = new ReflectionClass( $concrete );

        // 如果不能实例化,说明他是抽象的,抛出异常
        if ( ! $reflector->isInstantiable() ) {
            throw new BindingResolutionException( "[$concrete] 不能实例化" );
        }

        // 获取实体构造函数
        $constructor = $reflector->getConstructor();

        // 没有构造方法直接返回实例化
        if ( is_null( $constructor ) ) {
            return new $concrete;
        }

        // 获取构造函数的依赖参数
        $dependencies = $constructor->getParameters();

        // 根据依赖解析实例
        $instances = $this->resolveDependencies( $dependencies );

        return $reflector->newInstanceArgs( $instances );
    }

    /**
     * 解析构造函数的参数
     *
     * @param array $dependencies
     *
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    protected function resolveDependencies( array $dependencies )
    {
        $results = [];

        foreach ( $dependencies as $dependency ) {
            // 这里是获取在 with 数组中的参数
            if ( $this->hasParameterOverride( $dependency ) ) {
                $results[] = $this->getParameterOverride( $dependency );

                continue;
            }

            // 如果没在 with 中,那需要解析依赖
            // 有可能是基本数据类型如 str,int
            $results[] = is_null( $dependency->getClass() ) ? $this->resolvePrimitive( $dependency ) : $this->resolveClass( $dependency );
        }

        return $results;
    }

    /**
     * 是否存在依赖参数名
     *
     * @param \ReflectionParameter $dependency
     *
     * @return bool
     */
    protected function hasParameterOverride( $dependency )
    {
        return array_key_exists( $dependency->name, $this->getLastParameterOverride() );
    }

    /**
     * 获取依赖参数数组
     *
     * @return array|mixed
     */
    protected function getLastParameterOverride()
    {
        return count( $this->with ) ? end( $this->with ) : [];
    }

    /**
     * 获取依赖参数所对应的参数数组中键相同的值
     *
     * @param $dependency
     *
     * @return mixed
     */
    protected function getParameterOverride( $dependency )
    {
        return $this->getLastParameterOverride()[ $dependency->name ];
    }

    /**
     * 解析基础类型
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function resolvePrimitive( ReflectionParameter $parameter )
    {
        if ( $parameter->isDefaultValueAvailable() ) {
            return $parameter->getDefaultValue();
        }

        throw new BindingResolutionException( "无法解析类 [{$parameter->getDeclaringClass()->getName()}] 的依赖参数 [$parameter] " );
    }

    /**
     * 解析对象
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed|object
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    protected function resolveClass( ReflectionParameter $parameter )
    {
        try {
            return $this->make( $parameter->getClass()->name );
        } catch ( BindingResolutionException $exception ) {
            if ( $parameter->isOptional() ) {
                return $parameter->getDefaultValue();
            }

            throw $exception;
        }
    }

    /**
     * 设置抽象的别名
     *
     * @param $abstract
     * @param $alias
     */
    public function alias( $abstract, $alias )
    {
        $this->aliases[ $alias ] = $abstract;
    }

    /**
     * 给定的名字是否是别名
     *
     * @param $name
     *
     * @return bool
     */
    public function isAlias( $name )
    {
        return isset( $this->aliases[ $name ] );
    }

    /**
     * 获取抽象的别名
     *
     * @param $abstract
     *
     * @return mixed
     */
    public function getAlias( $abstract )
    {
        if ( ! isset( $this->aliases[ $abstract ] ) ) {
            return $abstract;
        }

        if ( $this->aliases[ $abstract ] === $abstract ) {
            throw new LogicException( "[$abstract] 的别名是自身" );
        }

        // 可能是为别名设置了别名
        // 这里需要自调用,直到获取最终那个没有别名的抽象名
        return $this->getAlias( $this->aliases[ $abstract ] );
    }

    /**
     * 数组式访问,抽象类型是否存在
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists( $offset )
    {
        return $this->bound( $offset );
    }

    /**
     * 组式访问, 解析抽象类型对应的实体
     *
     * @param mixed $offset
     *
     * @return mixed|object
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function offsetGet( $offset )
    {
        return $this->make( $offset );
    }

    /**
     * 绑定抽象类型
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        $this->bind( $offset, $value instanceof Closure ? $value : function () use ( $value ) {
            return $value;
        } );
    }

    /**
     * 删除抽象类型
     *
     * @param mixed $offset
     */
    public function offsetUnset( $offset )
    {
        unset( $this->bindings[ $offset ], $this->instances[ $offset ], $this->resolved[ $offset ] );
    }

}