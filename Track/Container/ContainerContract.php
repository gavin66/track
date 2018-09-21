<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/25
 * Time: 16:53
 */

namespace Track\Container;

interface ContainerContract
{
    /**
     * 给定的抽象类型是否已经绑定到容器
     *
     * @param $abstract
     *
     * @return bool
     */
    public function bound( $abstract );

    /**
     * 给抽象类型设置别名
     *
     * @param $abstract
     * @param $alias
     *
     * @return void
     */
    public function alias( $abstract, $alias );

    /**
     * 绑定服务到容器
     *
     * @param      $abstract
     * @param null $concrete
     * @param null $shared
     *
     * @return void
     */
    public function bind( $abstract, $concrete = null, $shared = null );

    /**
     * 绑定服务到容器(单例)
     *
     * @param      $abstract
     * @param null $concrete
     *
     * @return mixed
     */
    public function singleton( $abstract, $concrete = null );

    /**
     * 绑定一个实例到容器
     *
     * @param $abstract
     * @param $instance
     *
     * @return mixed
     */
    public function instance( $abstract, $instance );

    /**
     * 从容器中解析服务
     *
     * @param       $abstract
     * @param array $parameters
     *
     * @return mixed
     */
    public function make( $abstract, array $parameters = [] );

    /**
     * 给定的抽象类型是否已经解析过
     *
     * @param $abstract
     *
     * @return bool
     */
    public function resolved( $abstract );

    /**
     * 构建实例
     *
     * @param $concrete
     *
     * @return mixed
     */
    public function build( $concrete );

}