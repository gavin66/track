<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/9
 * Time: 16:47
 */

namespace Track\Routing;


class ControllerMiddlewareOptions
{
    /**
     * 中间件选项
     *
     * @var array
     */
    protected $options;

    /**
     * 创建中间件选项实例
     *
     * @param  array $options
     *
     * @return void
     */
    public function __construct( array &$options )
    {
        $this->options = &$options;
    }

    /**
     * 控制器中只有哪些方法运行中间件
     *
     * @param  array|string $methods
     *
     * @return $this
     */
    public function only( $methods )
    {
        $this->options[ 'only' ] = is_array( $methods ) ? $methods : func_get_args();

        return $this;
    }

    /**
     * 控制器中哪些方法不运行中间件
     *
     * @param  array|string $methods
     *
     * @return $this
     */
    public function except( $methods )
    {
        $this->options[ 'except' ] = is_array( $methods ) ? $methods : func_get_args();

        return $this;
    }
}