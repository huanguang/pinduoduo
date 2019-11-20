<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/11
 * Time: 23:55
 */

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Tools\Pay;

class PayController extends Controller
{
    /**
     * 订单支付
     * @param Request $request
     */
    public function payOrder(Request $request){

    }


    public function wxPayH5($order_sn){
        $order = Order::where('pdd_order_sn',$order_sn)->first();
        if (! $order) {
            return response()->json(['message'=>'订单不存在','status_code'=>422],422);
        }
        if ($order->pay_at) {
            return response()->json(['message'=>'订单已经支付','status_code'=>422],422);
        }
        $html = Pay::wxPayH5($order);
        echo $html;
    }
}