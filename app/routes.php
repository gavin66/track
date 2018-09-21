<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/4
 * Time: 14:56
 */

use Track\Facades\Route;
use App\Controllers\HelloController;

// index
Route::get( '/', HelloController::class . '@index' );
