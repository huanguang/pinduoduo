<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/11
 * Time: 23:24
 */

namespace App\Api\V1\Controllers;
use App\Api\V1\Controllers\Controllers;
use Illuminate\Http\Request;
use App\Tools\HttpRequest;
use App\Tools\Url;
use App\Api\V1\Requests\RequestAddress;
class UserController extends Controllers
{
    /**
     * 创建订单收货地址
     * @param Request $request
     */
    public function createAddress(Request $request, RequestAddress $requestAddress){
        $data = $request->all();
        $AccessToken = $data['access_token'];
        $user_id = $data['user_id'];
        $res = HttpRequest::send(sprintf(Url::$userAddressUrl,$user_id),[],$data,'POST',$AccessToken);
        if($res){
            return response()->json(['message'=>'请求成功','status_code'=>200,'data'=>$res]);
        }
        return response()->json(['message'=>'请求失败','status_code'=>500],500);
    }

    /**
     * 获取订单信息
     * @param Request $request
     */
    public function userInfo(Request $request){

    }

    /**
     * 获取城市信息
     * @param Request $request
     */
    public function getRegionsName(Request $request){
        $data = $request->all();
        $AccessToken = $data['access_token'];
        $user_id = $data['user_id'];
        $name = $data['name'];
        $p = $data['p'] ?? 1;
        $res = HttpRequest::send(sprintf(Url::$regionUrl,$p,$user_id),[],[],'GET',$AccessToken);
        if($res){
            $id = 0;
            foreach ($res['regions'] as $kk=>$vv){
                if($vv['region_name'] == $name){
                    $id = $vv['region_id'];
                }
            }
            return response()->json(['message'=>'请求成功','status_code'=>200,'data'=>['id'=>$id]]);
        }

        return response()->json(['message'=>'请求失败','status_code'=>500],500);
    }
}