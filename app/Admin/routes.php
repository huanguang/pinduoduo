<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    //商品路由
    $router->resource('goods/index', GoodssController::class); //商品路由
    //商品添加记录
    $router->resource('goods/add', GoodsController::class); //商品路由
    //店铺管理
    $router->resource('shop/index', ShopController::class); //店铺列表

    //买家管理
    $router->resource('buyer', BuyerController::class); //买家列表

    $router->resource('buyer/add', BuyerAddLogController::class); //添加买家

    //订单管理
    $router->resource('order', OrderController::class); //订单列表

    //收货地址
    $router->resource('address', BuyerAddressController::class); //收货地址列表

    //添加收货地址
    $router->resource('addressAdd', BuyerAddressAddController::class); //添加收货地址

    //商户

    $router->resource('merchant', MerchantController::class); //商户咧白哦
});
