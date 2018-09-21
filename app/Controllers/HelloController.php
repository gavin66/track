<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/9/21
 * Time: 10:38
 */

namespace App\Controllers;


use App\Middleware\Hello;
use Track\Http\Request;
use Track\Routing\Controller;

class HelloController extends Controller
{
    public function __construct()
    {
//        $this->middleware( Hello::class );
    }

    public function index( Request $request )
    {
        return 'Hello, Track! <br> Your IP: ' . $request->getClientIp();
    }
}