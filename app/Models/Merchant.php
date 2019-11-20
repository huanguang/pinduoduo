<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/14
 * Time: 17:06
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $table = 'merchant';

    /**
     * 关联操作记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function merchant_operation_log(){
        return $this->hasMany('App\Models\MerchantOperationLog','merchant_id','id');
    }
}