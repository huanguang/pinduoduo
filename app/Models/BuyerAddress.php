<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 16:18
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BuyerAddress extends Model
{
    protected $table = 'buyer_address';
    protected $fillable = ['id','district_id','district_name','city_id','city_name','province_id','province_name','created_at','updated_at','address','name','mobile','is_default','user_id'];
}