<?php

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

Route::get('/', function () {
    return view('welcome');
});
//登录
Route::get('user/login','Index\UserController@login');
Route::post('user/logins','UserController@logins');
//注册
Route::get('user/register','Index\UserController@register');
Route::post('user/registerinfo','Index\UserController@registerinfo');