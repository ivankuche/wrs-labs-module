<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('order/generate', [OrderController::class,"generate"]);
//Route::resource('order', OrderController::class);

Route::get('order/randomdata', [OrderController::class,"randomData"]);


Route::get('/', function () {
    return view('welcome');
});

https://wrs-labs-module.test/
