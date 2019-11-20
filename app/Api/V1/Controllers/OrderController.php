<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/11
 * Time: 23:16
 */

namespace App\Api\V1\Controllers;
use App\Api\V1\Controllers\Controllers;
use App\Models\Buyer;
use App\Models\BuyerAddress;
use App\Models\Goods;
use App\Models\Goodss;
use App\Models\Order;
use App\Models\Shop;
use App\Tools\OrderCode;
use App\Tools\Pay;
use Illuminate\Http\Request;
use App\Tools\HttpRequest;
use App\Tools\Url;
use App\Api\V1\Requests\RequestOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\Notice;

class OrderController extends Controllers
{
    /**
     * 获取订单列表
     * @param Request $request
     */
    public function index(Request $request){

    }

    /**
     * 创建订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request,RequestOrder $requestOrder){
        $data = $request->all();
        $money = $data['money'];
        //查找合适的商品
        $list = Goods::where('price',$money)->get();
        $goods_id = 0;
        $sku_id = 0;
        $group_id = 0;
        $shop_id = 0;
        $shop_name = '';
        $goods_name = '';
        $specs_value = '';
        if($list){
            foreach ($list as $item){
                //判断当前店铺和商品有没有被下架
                $shop = Shop::where('shop_id',$item->shop_id)
                    ->where('is_enable',1)
                    ->select('id','shop_name')
                    ->first();
                $goods = Goodss::where('goods_id',$item->goods_id)
                    ->where('is_enable',1)
                    ->select('id')
                    ->first();
                if($shop->id && $goods->id){
                    $goods_id = $item->goods_id;
                    $sku_id = $item->sku_id;
                    $group_id = $item->group_id;
                    $shop_id = $item->shop_id;
                    $shop_name = $shop->shop_name;
                    $specs_value = $item->specs_value;
                    $goods_name = $item->goods_name;
                    break;
                }

            }
        }
        if(!$goods_id || !$sku_id || !$group_id){
            return response()->json(['message'=>'没有合适的店铺/商品','status_code'=>422],422);
        }
        $newData['goods'][] = [
            'goods_id' => $goods_id,
            'sku_id' => $sku_id,
            'sku_number' => 1,
        ];
        $newData['group_id'] = $group_id;
        $newData['attribute_fields'] = [
            'order_amount' => $money,
        ];
        //寻找买家
       $Buyer = Buyer::where('is_enable',1)
            ->whereRaw('max_money>money')
            ->whereRaw('today_max_money>today_money')
            ->inRandomOrder()
            ->select('access_token','user_id','mobile')
            ->first();
        if(!$Buyer){
            return response()->json(['message'=>'没有合适的买家','status_code'=>422],422);
        }
        //查找买家的收货地址
        $BuyerAddress = BuyerAddress::where('user_id',$Buyer->user_id)
            ->inRandomOrder()
            ->first();
        if(!$BuyerAddress){
            return response()->json(['message'=>'没有合适的买家','status_code'=>422],422);
        }
        //DB::beginTransaction();//开启事务
        $newData['address_id'] = $BuyerAddress->address_id;
        //生成支付链接
        if($data['pay_type'] == 1){
            //支付宝支付
            $pay_code = 9;
        }else{
            //微信支付，只能是代付
            $pay_code = 38;
        }
        $newData['pay_app_id'] = $pay_code;
        $params = [
            'address_id' => $BuyerAddress->address_id,
            'attribute_fields' => [
                'order_amount' => $money,
            ],
            'goods' => [
                [
                    'goods_id' => $goods_id,
                    'sku_id' => $sku_id,
                    'sku_number' => 1,
                ]
            ],
            'group_id' => $group_id,
            'pay_app_id' => $pay_code,
        ];
        //保存订单信息


        $res =  HttpRequest::post(sprintf(Url::$createOrderUrl,$Buyer->user_id), json_encode($params, JSON_UNESCAPED_UNICODE),[
            'Content-type:application/json;charset=UTF-8;',
            'AccessToken:'.$Buyer->access_token
        ]);
        //获取商户信息
        $m = DB::table('merchant')->where('appid',$data['appid'])->select('id')->first();

        DB::beginTransaction();//开启事务
        try{
            $order_sn = Str::random(20);
            $addData = [
                'request_order_sn'=>$data['request_order_sn'],//外部订单号
                'order_sn'=>$order_sn,//当前平台订单号，自生成
                'pay_type'=>$data['pay_type'],
                'goods_id'=>$goods_id,
                'shop_id'=>$shop_id,
                'money'=>$money,
                'sku_id'=>$sku_id,
                'group_id'=>$group_id,
                'shop_name'=>$shop_name,
                'goods_name'=>$goods_name,
                'specs_value'=>$specs_value,
                'buyer_id'=>$Buyer->user_id,
                'buyer_name'=>$Buyer->mobile,
                'address_id'=>$BuyerAddress->address_id,
                'address'=>$BuyerAddress->district_name.$BuyerAddress->city_name.$BuyerAddress->province_name.$BuyerAddress->address,
                'address_name'=>$BuyerAddress->name,
                'address_mobile'=>$BuyerAddress->mobile,
                'api_status_str'=>'待付款',
                'type'=>$data['type'] ?? 1,
                'status'=>0,
                'notify_url'=>$data['notify_url'],
                'merchant_id'=>$m->id,
            ];

            Order::create($addData);
            //拼多多订单返回信息
            //$data = '"{"server_time":1573800659,"order_sn":"191115-098844672971228","group_order_id":"944098844672971228","order_amount":839900,"fp_id":"-O80UNsScSd1Cl62ZarBRUM6ck27PFDBmZgPoqvPLVU"}"';
            $res = json_decode($res,true);
            if(isset($res['error_msg']) && $res['error_msg']){
                return response()->json(['message'=>'PDD下单失败','status_code'=>422],422);
            }
            $pay_url = Pay::pay($pay_code,$res,$Buyer); //支付链接
            $h5_url = $pay_url;
            if($pay_code == 38){
                $h5_url = Pay::wxPayH5Url($res['order_sn']);
            }

            $order = [
                'order_sn' => $order_sn,
                'request_order_sn' => $data['request_order_sn'],
                'qrUrl' => $pay_url,
                'h5Url' => $h5_url,
            ];
            $upData = [
                'fp_id'=>$res['fp_id'],
                'group_order_id'=>$res['group_order_id'],
                'pdd_order_sn'=>$res['order_sn'],
                'order_amount'=>$res['order_amount'],
                'pay_url'=>$pay_url,
            ];
            //修改订单状态
            $r = Order::where('order_sn',$order_sn)->update($upData);
            if(!$r){
                DB::rollBack();
                return response()->json(['message'=>'下单失败2','status_code'=>422],422);
            }
            //保存订单记
            DB::commit();

            //延迟一分钟执行队列
            Notice::dispatch(Order::where('order_sn',$order_sn)->first())->delay(now()->addMinutes(1));
            return response()->json(['message'=>'下单成功','status_code'=>200,'data'=>$order], 200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json(['message'=>$exception->getMessage(),'status_code'=>422],422);
        }
    }

    /**
     * 获取订单详情
     * @param Request $request
     */
    public function details(Request $request){
        $data = $request->all();
        $order_sn = $data['order_sn'] ?? 0;
        if(!$order_sn){
            return response()->json(['message'=>'请求失败','status_code'=>422],422);
        }
        if(strpos($order_sn,'-') !== false){
            //可以
            $order = Order::where('pdd_order_sn',$order_sn)->select('buyer_id')->first();
            if($order){
                $user = Buyer::where('user_id',$order->buyer_id)->select('access_token')->first();
                if(isset($user->access_token)){
                    $data = HttpRequest::get(sprintf(Url::$orderDetailsUrl,$order_sn),["Cookie: PDDAccessToken={$user->access_token}"]);
                    $data = trimall($data);
                    $data = get_between($data,'window.rawData=',';window.leo');
                    //将截取的json格式字符串
                    $data = json_decode($data,true);
                    $newData = [
                        'payTime'=>$data['data']['payTime'],
                        'orderStatus'=>$data['data']['orderStatus'],
                        'chatStatusPrompt'=>$data['data']['chatStatusPrompt'],
                    ];
                    return response()->json(['message'=>'请求成功','status_code'=>200,'data'=>$newData],200);
                    //dump($data['data']['payStatus']);die;

//                    if (preg_match('/"chatStatusPrompt":"([^"]*)"/', $data, $matches)){
//                        return response()->json(['message'=>'请求失败','status_code'=>200,'data'=>$matches],200);
//                    }

                    //判断订单状态
                }
            }
        }
            return response()->json(['message'=>'请求失败','status_code'=>422],422);

    }
}