<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/11
 * Time: 23:57
 */

namespace App\Api\V1\Controllers;
use App\Api\V1\Controllers\Controllers;
use Illuminate\Http\Request;
use App\Tools\HttpRequest;
use App\Tools\Url;

class GoodsController extends Controllers
{
    /**
     * 商品详情
     * @param Request $request
     */
    public function details(Request $request){
        //获取请求头token
        $token = 'UT5VOLGET2CYICACLPCB6SZCOTVW2YSU56MOE5CYMT4U7GX3LRIA110cc70';
        $goods_id = '43324470870';
        //接收参数
        $data = $request->all();
        $res = HttpRequest::send2($data['url'],[],[],'GET',$token);
        if($res){
            return response()->json(['message'=>'请求成功','status_code'=>200,'data'=>['content'=>$res]]);
        }
        return response()->json(['message'=>'请求失败','status_code'=>500],500);
    }
}