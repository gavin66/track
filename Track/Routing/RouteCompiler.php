<?php

namespace Track\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

class RouteCompiler
{
    /**
     * The route instance.
     *
     * @var \Track\Routing\Route
     */
    protected $route;

    /**
     * Create a new Route compiler instance.
     *
     * @param  \Track\Routing\Route $route
     *
     * @return void
     */
    public function __construct( $route )
    {
        $this->route = $route;
    }

    /**
     * 编译路由
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function compile()
    {
        $optionals = $this->getOptionalParameters();

        $uri = preg_replace( '/\{(\w+?)\?\}/', '{$1}', $this->route->uri() );

        return ( new SymfonyRoute( $uri, $optionals, [], [ 'utf8' => true ], $this->route->getDomain() ? : '' ) )->compile();
    }

    /**
     * 获得路由可选参数.
     *
     * @return array
     */
    protected function getOptionalParameters()
    {
        preg_match_all( '/\{(\w+?)\?\}/', $this->route->uri(), $matches );

        return isset( $matches[ 1 ] ) ? array_fill_keys( $matches[ 1 ], null ) : [];
    }
}
