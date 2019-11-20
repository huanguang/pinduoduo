<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/14
 * Time: 19:51
 */

namespace App\Api\V1\Requests;
use Illuminate\Foundation\Http\FormRequest;

class RequestAddress extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address' => 'required',
            'city_id' => 'required',
            'name' => 'required',
            'sign' => 'required',
            'mobile' => 'required',
            'timestamp' => 'required',
            'district_id' => 'required',
            'province_id' => 'required',
            //'is_default' => 'required',
        ];
    }
}