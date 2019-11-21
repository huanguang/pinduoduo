<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 14:24
 */

namespace App\Tools;


class Url
{

    public static $loginUrl = 'https://api.pinduoduo.com/login';//登陆login

    public static $goodsDetailsUrl = 'https://mobile.yangkeduo.com/proxy/api/api/galen/v2/goods/%d';//获取商品详情

    public static $userAddressUrl = 'https://api.yangkeduo.com/api/origenes/address';//获取用户地址信息

    public static $shopDetailsUrl = 'https://api.pinduoduo.com/mall/%d/info';//获取店铺信息

    public static $regionUrl = 'https://mobile.yangkeduo.com/proxy/api/api/galen/v2/regions/%d?pdduid=%d';//获取省市区地址

    public static $createOrderUrl = 'https://mobile.yangkeduo.com/proxy/api/order?pdduid=%d';//下单

    public static $payUrl = 'https://mobile.yangkeduo.com/proxy/api/order/prepay?pdduid=%d';//支付宝url

    public static $payWeChatUrl = 'https://mobile.yangkeduo.com/friend_pay.html?fp_id=%s';//微信代付

    public static $payWeChatH5Url = '/pay/wePayH5/%d';//微信h5支付

    public static $orderListUrl = 'https://mobile.yangkeduo.com/proxy/api/api/aristotle/order_list?pdduid=%d';//订单列表

    //public static $orderDetailsUrl = 'https://mobile.yangkeduo.com/proxy/api/order/%s/received?pdduid=%d';//订单详情
    public static $orderDetailsUrl = 'https://mobile.yangkeduo.com/order.html?order_sn=%s';//订单详情

    public static $pay = 'https://mobile.yangkeduo.com/proxy/api/order/prepay?pdduid=%d';//支付url
}