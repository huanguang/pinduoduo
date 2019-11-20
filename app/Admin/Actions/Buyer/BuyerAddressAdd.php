<?php

namespace App\Admin\Actions\Buyer;

use App\Tools\Rsa;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Tools\AppPath;

class BuyerAddressAdd extends RowAction
{
    public $name = '添加收货地址';
    public function form()
    {
        $this->text('district_name', '省份')->required();
        $this->text('city_name', '城市')->required();
        $this->text('province_name', '县区')->required();
        $this->text('address', '详细地址')->required();
        $this->text('name', '收货人')->required();
        $this->mobile('mobile', '收货手机号码')->required();
        $this->select('is_default', '是否默认')->options(['0'=>'否','1'=>'是'])->required();
    }
    public function handle(Model $model, Request $request)
    {

        $district_name = $request->get('district_name');
        $city_name = $request->get('city_name');
        $province_name = $request->get('province_name');
        $address = $request->get('address');
        $name = $request->get('name');
        $mobile = $request->get('mobile');
        $is_default = $request->get('is_default');
        //获取城市，省份，县区所对应的id
        //内部调用获取商品详情接口
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = ['access_token' => $model->replicate()->access_token,'p'=>1,'user_id'=>$model->replicate()->user_id,'name'=>$district_name];
        $data['inside'] = 100;
        $data['timestamp'] = time();
        $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);
        $res = $dispatcher->json($data)->post('user/getRegionsName');
        if($res['status_code'] != 200){
            return $this->response()->error('获取省份信息失败');
        }

        $district_id = $res['data']['id'];
        $data = ['access_token' => $model->replicate()->access_token,'p'=>$district_id,'user_id'=>$model->replicate()->user_id,'name'=>$city_name];
        $data['inside'] = 100;
        $data['timestamp'] = time();
        $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);
        $res = $dispatcher->json($data)->post('user/getRegionsName');
        if($res['status_code'] != 200){
            return $this->response()->error('获取城市信息失败');
        }

        $city_id = $res['data']['id'];
        $data = ['access_token' => $model->replicate()->access_token,'p'=>$city_id,'user_id'=>$model->replicate()->user_id,'name'=>$province_name];
        $data['inside'] = 100;
        $data['timestamp'] = time();
        $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);

        $res = $dispatcher->json($data)->post('user/getRegionsName');

        if($res['status_code'] != 200){
            return $this->response()->error('获取县区信息失败');
        }
        $province_id = $res['data']['id'];

        //请求接口添加地址
        $params = [
            'address'=> $address,
            'city_id'=> $city_id,
            'name'=> $name,
            'mobile'=> $mobile,
            'district_id'=> $province_id,
            'province_id'=> $district_id,
            'is_default'=> $is_default,
        ];

        $data = ['access_token' => $model->replicate()->access_token,'user_id'=>$model->replicate()->user_id];
        $data = array_merge($data,$params);
        $data['inside'] = 100;
        $data['timestamp'] = time();
        $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);
        $res = $dispatcher->json($data)->post('user/createAddress');
        if($res['status_code'] != 200){
            return $this->response()->error('添加PDD地址失败');
        }
        //添加保存数据库
        $addData = [
            'district_id'=>$district_id,
            'district_name'=>$district_name,
            'city_id'=>$city_id,
            'city_name'=>$city_name,
            'province_id'=>$province_id,
            'province_name'=>$province_name,
            'address'=>$address,
            'name'=>$name,
            'mobile'=>$mobile,
            'is_default'=>$is_default,
            'user_id'=>$model->replicate()->user_id,
            'address_id'=>$res['data']['address_id'],
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s'),
        ];
        if($is_default == 1){
            DB::table('buyer_address')->where('user_id',$model->replicate()->user_id)->update(['is_default'=>0]);
        }
        $res = DB::table('buyer_address')->insert($addData);
        if(!$res){
            return $this->response()->error('PDD添加成功，本地保存失败');
        }
        return $this->response()->success('地址已添加')->refresh();
    }

}