<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/28
 * Time: 11:01
 */

namespace Track\Foundation;

//use Track\Container\ContainerContract as Container;

/**
 * 服务提供者
 *
 * Class ServiceProvider
 *
 * @package Track
 */
abstract class ServiceProvider
{
    /**
     * 全局容器
     *
     * @var \Track\Container\ContainerContract
     */
    public $container;

    public function __construct( $container )
    {
        $this->container = $container;
    }

    /**
     * 注册服务提供者
     *
     * @return void
     */
    public abstract function register();

    /**
     * 所有的服务提供者注册完成后执行
     */
    public function boot()
    {

    }
}