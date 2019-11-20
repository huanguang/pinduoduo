<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/17
 * Time: 0:21
 */

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
class ConfirmOrder extends Action
{
    public $name = '确认收货';

    protected $selector = '.confirm-order';

    public function handle(Request $request)
    {
        //获取时间
        $time = $request->get('num');
        if($time <= 0){
            $this->response()->error('时间格式不正确');
        }
        //入列确认收货
        \App\Jobs\ConfirmOrder::dispatch($time);

        return $this->response()->success('确认收货中...')->refresh();
    }

    public function form()
    {
        $this->text('num', '时间（分钟）')->rules('required|regex:/^\d+$/|integer', [
            'regex' => '必须是数字',
            'required' => '不能为空',
            'integer'   => '最少一分钟',
        ]);
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-dropbox confirm-order">确认收货</a>
HTML;
    }
}