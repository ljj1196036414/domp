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
Route::post('user/loginInfo','Index\UserController@loginInfo');
Route::get('user/sign','Index\UserController@sign');
//发送邮箱
Route::get('user/sendEmali','Index\UserController@sendEmali');
Route::get('user/enrolll/{key}','Index\UserController@enroll');

//githod
Route::get('user/githod','Index\UserController@githod');



//注册
Route::get('user/register','Index\UserController@register');
Route::post('user/registerinfo','Index\UserController@registerinfo');
Route::get('user/hello','Index\UserController@hello');
//首页
Route::get('goods/index','Index\GoodsController@index');
Route::get('goods/search','Index\GoodsController@search');
Route::get('goods/item','Index\GoodsController@item');
Route::get('goods/iteminfo','Index\GoodsController@iteminfo');
//ajax 购物车
Route::get('goods/car','Index\GoodsController@car');
//订单
Route::get('goods/order','Index\GoodsController@order');
Route::get('goods/cart','Index\GoodsController@cart');
Route::get('goods/add','Index\GoodsController@add');

//支付
Route::get('pay/ali','Index\PayController@aliPay');



Route::get('goods/getMany','Index\GoodsController@getMany');
Route::get('goods/guzzleIesr1','Index\GoodsController@guzzleIesr1');
