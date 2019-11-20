<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 15:42
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Shop extends Model
{
    protected $table = 'shop';

    /**
     * 关联店铺操作记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shop_operation_log(){
        return $this->hasMany('App\Models\ShopOperationLog','shop_id','id');
    }
}