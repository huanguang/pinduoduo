<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/11
 * Time: 23:58
 */

namespace App\Api\V1\Controllers;
use App\Api\V1\Controllers\Controllers;
use Illuminate\Http\Request;
use App\Tools\HttpRequest;
use App\Tools\Url;

class ShopController extends Controllers
{
    /**
     * 店铺信息
     * @param Request $request
     */
    public function details(Request $request){
        $token = 'UT5VOLGET2CYICACLPCB6SZCOTVW2YSU56MOE5CYMT4U7GX3LRIA110cc70';
        $mall_id = '565389076';
        $res = HttpRequest::send(sprintf(Url::$shopDetailsUrl,$mall_id),[],[],'POST',$token);
        if($res){
            return response()->json(['message'=>'请求成功','status_code'=>200,'data'=>$res]);
        }
        return response()->json(['message'=>'请求失败','status_code'=>500],500);
    }
}