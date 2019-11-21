<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/15
 * Time: 12:11
 */

namespace App\Tools;
use App\Models\Buyer;
use App\Tools\HttpRequest;
use App\Tools\Url;
use GuzzleHttp\Client;

class Pay
{
    /**
     * 支付宝支付
     */
    private static function alipay($order_sn,$user)
    {
        $params = [
            'order_sn' => $order_sn,
            'version' => 3,
            'attribute_fields' => [
                'paid_times' => 0,
                'forbid_contractcode' => '1',
                'forbid_pappay' => '1',
            ],
            'return_url' => 'https://mobile.yangkeduo.com/transac_wappay_callback.html?order_sn='.$order_sn,
            'app_id' => 9,
        ];
        $client = new Client();
        $response = $client->request('POST',sprintf(Url::$payUrl,$user->user_id),[
            'json'=>$params,
            'headers' => [
                'Content-type'=> 'application/json;charset=UTF-8',
                'AccessToken'=> $user->access_token,
            ]

        ]);
        $result = json_decode($response->getBody()->getContents(),true);
        //$result = HttpRequest::send(sprintf(Url::$payUrl,$user->user_id),[], $params,'POST',$user->access_token);
        return $result['gateway_url'] . '?' . http_build_query($result['query']);
    }

    /**
     * 微信支付
     */
    public static function wepay($fp_id)
    {
        return sprintf(Url::$payWeChatUrl,$fp_id);
    }

    /**
     * 支付
     */
    public static function pay($pay_type, $order,$user)
    {
        if ($pay_type == 38) {
            return self::wepay($order['fp_id']);
        }
        if ($pay_type == 9) {
            return self::alipay($order['order_sn'],$user);
        }
    }

    /**
     * h5支付宝支付
     */
    public static function alipay_h5($order_sn,$user)
    {
        return self::alipay($order_sn,$user);
    }

    /**
     * h5微信支付
     */
    public static function wxPayH5($order)
    {
        $params = [
            'order_sn' => $order->pdd_order_sn,
            'version' => '3',
            'attribute_fields' => [
                'paid_times' => 0
            ],
            'pap_pay' => 1,
            'app_id' => 38
        ];
        $buyer = Buyer::where('user_id',$order->buyer_id)->select('access_token')->first();
        $result = HttpRequest::post(sprintf(Url::$pay,$order->buyer_id), json_encode($params, JSON_UNESCAPED_UNICODE),['AccessToken:' . $buyer->access_token,
            'Content-Type:application/json;charset=UTF-8',]);
        $result = json_decode($result,true);
        if (isset($result['error_code']) && $result['error_code'] > 0) {

        }
        $url = $result['mweb_url'];
        $headers = [
            'Referer: https://mobile.yangkeduo.com/transac_wechat_wapcallback.html?order_sn='.$order->pdd_order_sn.''
        ];
        return HttpRequest::get($url, $headers);
    }

    /**
     * h5微信支付url
     */
    public static function wxPayH5Url($order_sn)
    {
        return sprintf(
            "%s://%s",
            'https',
            $_SERVER['HTTP_HOST']
        ).sprintf(Url::$payWeChatH5Url,$order_sn);
    }
}