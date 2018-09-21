<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 11:34
 */

namespace Track\Foundation;

use Track\Container\ContainerContract as Container;
use Track\Facades\Facade;

class RegisterFacades
{
    public function bootstrap( Container $app )
    {
        Facade::setContainer( $app );
    }
}