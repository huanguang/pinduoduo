<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 11:30
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Buyer extends Model
{
    protected $table = 'buyer';
    protected $fillable = ['id','user_id','access_token','mobile','type','created_at','updated_at','money','today_money','today_max_money','max_money','ip','add_id'];

    /**
     * 关联买家操作记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buyer_operation_log(){
        return $this->hasMany('App\Models\BuyerOperationLog','buyer_id','id');
    }
}