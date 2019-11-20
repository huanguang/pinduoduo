<?php

namespace App\Http\Middleware;

use App\Tools\HttpRequest;
use Closure;
use App\Tools\Rsa;
use Illuminate\Support\Facades\DB;
use App\Tools\AppPath;
use App\Tools\CreateXml;
use App\Models\Order;
use App\Jobs\Notice;
use App\Tools\Md5;
class VerifySign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data = $request->all();
        if($request->getContentType() != 'json'){
            return response()->json('',403);
        }
        if(!isset($data['sign']) || !$data['sign']) {
            return response()->json(['message' => 'sign参数错误', 'status_code' => 422], 422);
        }
        if(isset($data['inside'])){
            //内部调用
            $path = AppPath::APP_RSA_PATH;
            $cer = AppPath::APP_RSA_CER_PATH;
            $pfx = AppPath::APP_RSA_PFX_PATH;
            $pass = AppPath::APP_RSA_PASS_PATH;
            $sign = $data['sign'];
            unset($data['sign']);
//            if(Rsa::decode($sign,$data,$cer) !== 1){
//                return response()->json(['message'=>'签名错误','status_code'=>422],422);
//            }
        }else{
            if(isset($data['appid']) && $data['appid']) {
                $info = DB::table('merchant')->where('appid',$data['appid'])->select('appid','merchant','secret_key')->first();
                if(!$info){
                    return response()->json(['message' => 'appid错误', 'status_code' => 422], 422);
                }
                //验证订单号是否存在
                $order = DB::table('order')->where('request_order_sn',$data['request_order_sn'])->select('id')->first();
                if(isset($order->id)){
                    return response()->json(['message' => '订单号已存在', 'status_code' => 422], 422);
                }
//                $path = $info->key_url;
//                $cer = $path.$info->merchant.'.cer';
//                $pfx = $path.$info->merchant.'.pfx';
//                $pass = $info->pass;
            }else{
                return response()->json(['message' => '参数错误', 'status_code' => 422], 422);
            }
            //外部请求改用MD5加appid和key的验签方式
            $sign = $data['sign'];
            unset($data['sign']);
            //$sign = Md5::encode($data,$info->appid,$info->secret_key.'dasdas');
            if(Md5::decode($sign,$data,$info->appid,$info->secret_key) !== true){
                return response()->json(['message'=>'签名错误','status_code'=>422],422);
            }

        }

        return $next($request);
    }
}
