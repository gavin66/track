<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/18
 * Time: 10:54
 */

namespace Track\Support;

/**
 * 字符串操作
 *
 * @package Track\Support
 */
class Str
{

    /**
     * 返回字符串给定值之后的所有内容.
     *
     * @param  string $subject
     * @param  string $search
     *
     * @return string
     */
    public static function after( $subject, $search )
    {
        return $search === '' ? $subject : array_reverse( explode( $search, $subject, 2 ) )[ 0 ];
    }

    /**
     * 返回字符串中给定值之前的所有内容
     *
     * @param  string $subject
     * @param  string $search
     *
     * @return string
     */
    public static function before( $subject, $search )
    {
        return $search === '' ? $subject : explode( $search, $subject )[ 0 ];
    }

    /**
     * 判断给定的字符串是否包含给定的值
     *
     * @param  string       $haystack
     * @param  string|array $needles
     *
     * @return bool
     */
    public static function contains( $haystack, $needles )
    {
        foreach ( (array)$needles as $needle ) {
            if ( $needle !== '' && mb_strpos( $haystack, $needle ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断给定的字符串的结尾是否是指定值
     *
     * @param  string       $haystack
     * @param  string|array $needles
     *
     * @return bool
     */
    public static function endsWith( $haystack, $needles )
    {
        foreach ( (array)$needles as $needle ) {
            if ( substr( $haystack, -strlen( $needle ) ) === (string)$needle ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 返回给定字符串的长度
     *
     * @param  string $value
     * @param  string $encoding
     *
     * @return int
     */
    public static function length( $value, $encoding = null )
    {
        if ( $encoding ) {
            return mb_strlen( $value, $encoding );
        }

        return mb_strlen( $value );
    }

    /**
     * 限制给定字符串的长度
     *
     * @param  string $value
     * @param  int    $limit
     * @param  string $end
     *
     * @return string
     */
    public static function limit( $value, $limit = 100, $end = '...' )
    {
        if ( mb_strwidth( $value, 'UTF-8' ) <= $limit ) {
            return $value;
        }

        return rtrim( mb_strimwidth( $value, 0, $limit, '', 'UTF-8' ) ) . $end;
    }

    /**
     * 返回给定字符串的小写
     *
     * @param  string $value
     *
     * @return string
     */
    public static function lower( $value )
    {
        return mb_strtolower( $value, 'UTF-8' );
    }

    /**
     * 限制给定字符串的单词数
     *
     * @param  string $value
     * @param  int    $words
     * @param  string $end
     *
     * @return string
     */
    public static function words( $value, $words = 100, $end = '...' )
    {
        preg_match( '/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches );

        if ( ! isset( $matches[ 0 ] ) || static::length( $value ) === static::length( $matches[ 0 ] ) ) {
            return $value;
        }

        return rtrim( $matches[ 0 ] ) . $end;
    }

    /**
     * 解析 class@method
     *
     * @param  string      $callback
     * @param  string|null $default
     *
     * @return array
     */
    public static function parseCallback( $callback, $default = null )
    {
        return static::contains( $callback, '@' ) ? explode( '@', $callback, 2 ) : [ $callback, $default ];
    }

    /**
     * 生成随机字符串
     *
     * @param int $length
     *
     * @return string
     * @throws \Exception
     */
    public static function random( $length = 16 )
    {
        $string = '';

        while ( ( $len = strlen( $string ) ) < $length ) {
            $size = $length - $len;

            $bytes = openssl_random_pseudo_bytes( $size );

            $string .= substr( str_replace( [ '/', '+', '=' ], '', base64_encode( $bytes ) ), 0, $size );
        }

        return $string;
    }

    /**
     * 用数组依次替换字符串中的值
     *
     * $string = '该活动将于 ? 至 ? 之间举行';
     * $replaced = replaceArray('?', ['8:30', '9:00'], $string);
     * 该活动将于 8:30 至 9:00 之间举行
     *
     * @param  string $search
     * @param  array  $replace
     * @param  string $subject
     *
     * @return string
     */
    public static function replaceArray( $search, array $replace, $subject )
    {
        foreach ( $replace as $value ) {
            $subject = static::replaceFirst( $search, $value, $subject );
        }

        return $subject;
    }

    /**
     * 替换字符串中给定值的第一个匹配项
     *
     * @param  string $search
     * @param  string $replace
     * @param  string $subject
     *
     * @return string
     */
    public static function replaceFirst( $search, $replace, $subject )
    {
        if ( $search == '' ) {
            return $subject;
        }

        $position = strpos( $subject, $search );

        if ( $position !== false ) {
            return substr_replace( $subject, $replace, $position, strlen( $search ) );
        }

        return $subject;
    }

    /**
     * 替换字符串中给定值的最后一个匹配项
     *
     * @param  string $search
     * @param  string $replace
     * @param  string $subject
     *
     * @return string
     */
    public static function replaceLast( $search, $replace, $subject )
    {
        $position = strrpos( $subject, $search );

        if ( $position !== false ) {
            return substr_replace( $subject, $replace, $position, strlen( $search ) );
        }

        return $subject;
    }

    /**
     * 将给定值的单个实例添加到字符串（如果它尚未以值开始）
     *
     * $adjusted = start('this/string', '/');
     * /this/string
     * $adjusted = start('/this/string/', '/');
     * /this/string
     *
     * @param  string $value
     * @param  string $prefix
     *
     * @return string
     */
    public static function start( $value, $prefix )
    {
        $quoted = preg_quote( $prefix, '/' );

        return $prefix . preg_replace( '/^(?:' . $quoted . ')+/u', '', $value );
    }

    /**
     * 转换大写
     *
     * @param  string $value
     *
     * @return string
     */
    public static function upper( $value )
    {
        return mb_strtoupper( $value, 'UTF-8' );
    }

    /**
     * 字符串转换为首字母大写
     *
     * $converted = title_case('a nice title uses the correct case');
     * A Nice Title Uses The Correct Case
     *
     * @param  string $value
     *
     * @return string
     */
    public static function title( $value )
    {
        return mb_convert_case( $value, MB_CASE_TITLE, 'UTF-8' );
    }

    /**
     * 字符串是否以给定子字符串开头
     *
     * @param  string       $haystack
     * @param  string|array $needles
     *
     * @return bool
     */
    public static function startsWith( $haystack, $needles )
    {
        foreach ( (array)$needles as $needle ) {
            if ( $needle !== '' && substr( $haystack, 0, strlen( $needle ) ) === (string)$needle ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 指定开始位置与长度,返回子字符串
     *
     * @param  string   $string
     * @param  int      $start
     * @param  int|null $length
     *
     * @return string
     */
    public static function substr( $string, $start, $length = null )
    {
        return mb_substr( $string, $start, $length, 'UTF-8' );
    }

    /**
     * 字符串的第一个字符大写
     *
     * @param  string $string
     *
     * @return string
     */
    public static function ucfirst( $string )
    {
        return static::upper( static::substr( $string, 0, 1 ) ) . static::substr( $string, 1 );
    }

    /**
     * 将字符串转换为首字母大写的异种驼峰表示
     *
     *  unusualCamel('foo-bar) = 'FooBar'
     *  unusualCamel('foo_bar) = 'FooBar'
     *  unusualCamel('foo bar) = 'FooBar'
     *
     * @param  string $value
     *
     * @return string
     */
    public static function unusualCamel( $value )
    {
        $value = ucwords( str_replace( [ '-', '_' ], ' ', $value ) );

        return str_replace( ' ', '', $value );
    }

    /**
     * 将字符串转换为驼峰表示
     *
     * camel('foo-bar') = 'fooBar'
     * camel('foo_bar') = 'fooBar'
     * camel('foo bar') = 'fooBar'
     *
     * @param  string $value
     *
     * @return string
     */
    public static function camel( $value )
    {
        return lcfirst( static::unusualCamel( $value ) );
    }
}