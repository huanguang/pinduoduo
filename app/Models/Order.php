<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 14:09
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    protected $table = 'order';
    protected $fillable = ['order_sn','request_order_sn','pay_type','pay_at','goods_id','goods_name','specs_value','created_at','updated_at','shop_id','money','sku_id','group_id','shop_name','goods_name','specs_value','buyer_id','buyer_name','address_id','address','address_name','address_mobile','api_status_str','type','pdd_order_sn','fp_id','group_order_id','order_amount','pay_url','merchant_id','is_calculation','notify_url'];

    /**
     * 关联订单操作记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order_operation_log(){
        return $this->hasMany('App\Models\OrderOperationLog','order_id','id');
    }
}