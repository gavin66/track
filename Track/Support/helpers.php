<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/9
 * Time: 14:40
 *
 * 自定义函数库,方便于 Web 开发
 * 此文件 composer 自动加载
 *
 */

use Track\Config\Repository;
use Track\Application;
use Track\Support\Arr;
use Track\Support\Str;
use Track\Support\Collection;
use \Track\Http\ResponseFactoryContract;

if ( ! function_exists( 'app' ) ) {
    /**
     * 获取容器内实例
     *
     * @param  string $abstract
     * @param  array  $parameters
     *
     * @return mixed|\Track\Application
     */
    function app( $abstract = null, array $parameters = [] )
    {
        if ( is_null( $abstract ) ) {
            return Application::getInstance();
        }

        return call_user_func( [ Application::getInstance(), 'make' ], $abstract, $parameters );
    }
}

if ( ! function_exists( 'config' ) ) {
    /**
     * 获取/设置 配置项
     *
     * @param  array|string $key
     * @param  mixed        $default
     *
     * @return mixed| Repository
     */
    function config( $key = null, $default = null )
    {
        if ( is_null( $key ) ) {
            return app( 'config' );
        }

        if ( is_array( $key ) ) {
            return app( 'config' )->set( $key );
        }

        return app( 'config' )->get( $key, $default );
    }
}

if ( ! function_exists( 'session' ) ) {
    /**
     * 获取或者设置 session 值
     *
     * @param null $key
     * @param null $default
     *
     * @return mixed|\Track\Session\Store|\Track\Session\SessionManager
     */
    function session( $key = null, $default = null )
    {
        if ( is_null( $key ) ) {
            return app( 'session' );
        }

        if ( is_array( $key ) ) {
            return app( 'session' )->put( $key );
        }

        return app( 'session' )->get( $key, $default );
    }
}

if ( ! function_exists( 'value' ) ) {
    /**
     * 返回默认值
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    function value( $value )
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if ( ! function_exists( 'array_get' ) ) {
    /**
     * 使用 "." 来获取数组键值
     *
     * @param  \ArrayAccess|array $array
     * @param  string             $key
     * @param  mixed              $default
     *
     * @return mixed
     */
    function array_get( $array, $key, $default = null )
    {
        return Arr::get( $array, $key, $default );
    }
}

if ( ! function_exists( 'env' ) ) {
    /**
     * 获取环境变量值
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    function env( $key, $default = null )
    {
        $value = getenv( $key );

        if ( $value === false ) {
            return value( $default );
        }

        switch ( strtolower( $value ) ) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'empty':
                return '';
            case 'null':
                return null;
        }

        if ( strlen( $value ) > 1 && Str::startsWith( $value, '"' ) && Str::endsWith( $value, '"' ) ) {
            return substr( $value, 1, -1 );
        }

        return $value;
    }
}

if ( ! function_exists( 'data_get' ) ) {
    /**
     * 使用 "." 记号获取数组值
     *
     * @param  mixed        $target
     * @param  string|array $key
     * @param  mixed        $default
     *
     * @return mixed
     */
    function data_get( $target, $key, $default = null )
    {
        if ( is_null( $key ) ) {
            return $target;
        }

        $key = is_array( $key ) ? $key : explode( '.', $key );

        while ( ! is_null( $segment = array_shift( $key ) ) ) {
            if ( $segment === '*' ) {
                if ( ! is_array( $target ) ) {
                    return value( $default );
                }

                $result = Arr::pluck( $target, $key );

                return in_array( '*', $key ) ? Arr::collapse( $result ) : $result;
            }

            if ( Arr::accessible( $target ) && Arr::exists( $target, $segment ) ) {
                $target = $target[ $segment ];
            } elseif ( is_object( $target ) && isset( $target->{$segment} ) ) {
                $target = $target->{$segment};
            } else {
                return value( $default );
            }
        }

        return $target;
    }
}

if ( ! function_exists( 'collect' ) ) {
    /**
     * 创建一个集合
     *
     * @param  mixed $value
     *
     * @return Collection
     */
    function collect( $value = null )
    {
        return new Collection( $value );
    }
}

if ( ! function_exists( 'session_unserialize' ) ) {
    /**
     * 反序列化 session 数据,返回数组
     *
     * @param $session_str
     *
     * @return array
     */
    function session_unserialize( $session_str )
    {
        $method = ini_get( "session.serialize_handler" );
        switch ( $method ) {
            case "php":
                $return_data = [];
                $offset      = 0;
                while ( $offset < strlen( $session_str ) ) {
                    if ( ! strstr( substr( $session_str, $offset ), "|" ) ) {
                        throw new InvalidArgumentException( "无效的数据: " . substr( $session_str, $offset ) );
                    }
                    $pos                     = strpos( $session_str, "|", $offset );
                    $num                     = $pos - $offset;
                    $varname                 = substr( $session_str, $offset, $num );
                    $offset                  += $num + 1;
                    $data                    = unserialize( substr( $session_str, $offset ) );
                    $return_data[ $varname ] = $data;
                    $offset                  += strlen( serialize( $data ) );
                }

                return $return_data;
                break;
            case "php_binary":
                $return_data = [];
                $offset      = 0;
                while ( $offset < strlen( $session_str ) ) {
                    $num                     = ord( $session_str[ $offset ] );
                    $offset                  += 1;
                    $varname                 = substr( $session_str, $offset, $num );
                    $offset                  += $num;
                    $data                    = unserialize( substr( $session_str, $offset ) );
                    $return_data[ $varname ] = $data;
                    $offset                  += strlen( serialize( $data ) );
                }

                return $return_data;
                break;
            default:
                throw new InvalidArgumentException( "不支持的 session.serialize_handler: " . $method . ". 只支持: php, php_binary" );
        }
    }
}


if ( ! function_exists( 'encrypt' ) ) {
    /**
     * 加密
     *
     * @param  mixed $value
     *
     * @return string
     */
    function encrypt( $value )
    {
        return app( 'encrypter' )->encrypt( $value );
    }
}

if ( ! function_exists( 'decrypt' ) ) {
    /**
     * 解密
     *
     * @param  string $value
     *
     * @return mixed
     */
    function decrypt( $value )
    {
        return app( 'encrypter' )->decrypt( $value );
    }
}

if ( ! function_exists( 'route_action' ) ) {
    /**
     * 返回路由定义的 action 参数
     *
     * @param $class  string
     * @param $method string
     *
     * @return string
     */
    function route_action( $class, $method )
    {
        return sprintf( '%s@%s', $class, $method );
    }
}

if ( ! function_exists( 'response' ) ) {
    /**
     * 返回响应
     *
     * @param  string|array $content
     * @param  int          $status
     * @param  array        $headers
     *
     * @return \Track\Http\ResponseFactoryContract
     */
    function response( $content = '', $status = 200, array $headers = [] )
    {
        $factory = app( ResponseFactoryContract::class );

        if ( func_num_args() === 0 ) {
            return $factory;
        }

        return call_user_func( [ $factory, 'make' ], $content, $status, $headers );
    }
}

if ( ! function_exists( 'http_request' ) ) {
    /**
     * todo
     * 发送 http 请求
     *
     * @param string $method
     * @param        $url
     * @param array  $headers
     * @param null   $body
     * @param string $version
     *
     * @return string
     * @throws Exception
     */
    function http_request( $url, $method = 'GET', array $headers = [], $body = null, $version = '1.1' )
    {
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTP_VERSION, $version == '1.1' ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0 );
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        if ( Str::upper( $method ) === 'POST' ) {
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        }

        $result = curl_exec( $ch );

        curl_close( $ch );

        return $result;
    }
}