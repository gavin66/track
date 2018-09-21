<?php

namespace Track\Routing\Matching;

use Track\Http\Request;
use Track\Routing\Route;

class HostValidator implements ValidatorInterface
{
    /**
     * Validate a given rule against a route and request.
     *
     * @param  \Track\Routing\Route $route
     * @param  \Track\Http\Request  $request
     *
     * @return bool
     */
    public function matches( Route $route, Request $request )
    {
        if ( is_null( $route->getCompiled()->getHostRegex() ) ) {
            return true;
        }

        return preg_match( $route->getCompiled()->getHostRegex(), $request->getHost() );
    }
}
