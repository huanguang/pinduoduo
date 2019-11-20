<?php
namespace App\Models;
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 15:39
 */
use Illuminate\Database\Eloquent\Model;
class Goods extends Model
{
    protected $table = 'goods';
    protected $fillable = ['id','shop_id','goods_id','sku_id','group_id','goods_name','specs_value','created_at','updated_at','url','price','goodss_id','add_id'];
}