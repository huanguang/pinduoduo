<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');
/**
 * 所有路由组限制每分钟请求速度60次（每个路由每分钟限制60次）
 */
$api->version('v1',['middleware' => 'api.throttle', 'limit' => 60, 'expires' => 1],function ($api){
    /**
     * 所有中间件校验签名
     */
    $api->group(['middleware'=>'verifySign'],function ($api){
        $api->get('/index','App\Api\V1\Controllers\IndexController@index');

        /**
         * 请求获取token
         */
        $api->post('/token',['middleware' => 'Authentication', 'App\Api\V1\Controllers\IndexController@index']);

        /**
         * 用户路由组
         */
        $api->group(['prefix' => '/user'],function ($api){
            $api->post('/createAddress', 'App\Api\V1\Controllers\UserController@createAddress');
            $api->post('/getRegionsName', 'App\Api\V1\Controllers\UserController@getRegionsName');//获取PDD城市地址id
        });
        /**
         * 商品路由组
         */
        $api->group(['prefix' => '/goods'],function ($api){
            //获取商品详情
            $api->post('/details', 'App\Api\V1\Controllers\GoodsController@details');
        });

        /**
         * 店铺路由组
         */
        $api->group(['prefix' => '/shop'],function ($api){
            //获取店铺信息
            $api->get('/details', 'App\Api\V1\Controllers\ShopController@details');
        });
        /**
         * 订单路由组
         */
        $api->group(['prefix' => '/order'],function ($api){
            $api->post('/create', 'App\Api\V1\Controllers\OrderController@create');//创建订单
            $api->get('/received', 'App\Api\V1\Controllers\OrderController@received');//确认收货
            $api->get('/evaluate', 'App\Api\V1\Controllers\OrderController@evaluate');//评价
            $api->post('/details', 'App\Api\V1\Controllers\OrderController@details');//获取订单详情
        });
        /**
         * 支付组路由
         */
        //$api->group(['prefix' => '/pay'],function ($api){
            //$api->get('/wePayH5/{order_sn}', 'App\Api\V1\Controllers\PayController@wxPayH5');//微信h5支付
        //});
    });
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
