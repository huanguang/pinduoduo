<?php
namespace App\Models;
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 15:39
 */
use Illuminate\Database\Eloquent\Model;
class Goodss extends Model
{
    protected $table = 'goodss';

    /**
     * 关联商品sku详细记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goods_sku(){
        return $this->hasMany('App\Models\Goods','goodss_id','id');
    }
}