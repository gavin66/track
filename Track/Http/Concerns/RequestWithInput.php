<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/1
 * Time: 12:13
 */

namespace Track\Http\Concerns;


use Track\Support\Arr;

trait RequestWithInput
{

    /**
     * 获取一个请求的数据
     *
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    public function input( $key = null, $default = null )
    {
        return data_get(
            $this->getInputSource()->all() + $this->query->all(), $key, $default
        );
    }

    /**
     * 获取请求的所有数据或指定的数据
     *
     * @param null $keys
     *
     * @return array
     */
    public function all( $keys = null )
    {
        $input = array_replace_recursive( $this->input(), $this->allFiles() );

        if ( ! $keys ) {
            return $input;
        }

        $results = [];

        foreach ( is_array( $keys ) ? $keys : func_get_args() as $key ) {
            Arr::set( $results, $key, Arr::get( $input, $key ) );
        }

        return $results;
    }

    /**
     * 判断请求的数据是否有指定的键
     *
     * @param $key
     *
     * @return bool
     */
    public function has( $key )
    {
        $keys = is_array( $key ) ? $key : func_get_args();

        $input = $this->all();

        foreach ( $keys as $value ) {
            if ( ! Arr::has( $input, $value ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @param $key
     *
     * @return mixed
     */
    public function exists( $key )
    {
        return $this->has( $key );
    }

    /**
     * 获取指定 header 值
     *
     * @param null $key
     * @param null $default
     *
     * @return mixed
     */
    public function header( $key = null, $default = null )
    {
        return $this->retrieveItem( 'headers', $key, $default );
    }

    /**
     * 从数据源中获得数据
     *
     * @param $source
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    protected function retrieveItem( $source, $key, $default )
    {
        if ( is_null( $key ) ) {
            return $this->$source->all();
        }

        return $this->$source->get( $key, $default );
    }

    /**
     * 获取所有文件
     *
     * @return mixed
     */
    public function allFiles()
    {
        $files = $this->files->all();

        return $files;
    }
}