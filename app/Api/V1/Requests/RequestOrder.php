<?php
namespace App\Api\V1\Requests;
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/14
 * Time: 16:09
 */
use Illuminate\Foundation\Http\FormRequest;

class RequestOrder extends FormRequest
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
            'money' => 'required',
            'pay_type' => 'required',
            'request_order_sn' => 'required',
            'sign' => 'required',
            'notify_url' => 'required',
            'timestamp' => 'required|max:10|min:10',
        ];
    }
}