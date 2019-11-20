<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 21:57
 */

namespace App\Admin\Actions\Order;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TongZhiAll extends Action
{
    protected $selector = '.tong-zhi-all';

    public function handle(Request $request)
    {
        $list = DB::table('order')->where('is_notice',3)->select('id','request_order_sn','api_status_str','status','pay_at','order_amount','notify_url','merchant_id')->get();
        if($list){
            foreach ($list as $item){
                //查询所有失败的订单，补发通知
                $url = $item->notify_url;
                //生成xml数据
                $msg['order_sn'] = $item->request_order_sn;
                $msg['msg'] = $item->api_status_str;
                $msg['orderStatus'] = $item->status;
                $msg['payTime'] = $item->pay_at;
                $msg['orderAmount'] = $item->order_amount;
                $msg['url'] = $url;
                $msg['order'] = $item;
                //延迟一分钟执行队列
                \App\Jobs\PostOrderXml::dispatch($msg);
                //记录操作日志
                DB::table('order_operation_log')
                    ->insert([
                        'admin_id'=>Admin::user()->id,
                        'order_id'=>$item->id,
                        'content'=>'一键补发失败通知',
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]);
            }
        }
        return $this->response()->success('执行成功....')->refresh();
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-danger tong-zhi-all"><i class='fa fa-bullhorn'></i>一键补发通知</a>
HTML;
    }
}