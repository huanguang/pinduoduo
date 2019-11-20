<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 21:27
 */

namespace App\Admin\Actions\Order;
use Carbon\Carbon;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;

class TongZhi extends BatchAction
{
    protected $selector = '.report-tong';

    public function handle(Collection $collection, Request $request)
    {
        $reason = $request->get('reason');
        foreach ($collection as $model) {
            $msg = [];
            $model->reason = $reason;
            $model->save();
            $url = $model->notify_url;
            //生成xml数据
            $msg['order_sn'] = $model->request_order_sn;
            $msg['msg'] = $model->api_status_str;
            $msg['orderStatus'] = $model->status;
            $msg['payTime'] = $model->pay_at;
            $msg['orderAmount'] = $model->order_amount;
            $msg['url'] = $url;
            $msg['order'] = $model;
            //记录操作日志
            DB::table('order_operation_log')
                ->insert([
                    'admin_id'=>Admin::user()->id,
                    'order_id'=>$model->id,
                    'content'=>$reason,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
            //延迟一分钟执行队列
            \App\Jobs\PostOrderXml::dispatch($msg);
        }

        return $this->response()->success('通知已补发！')->refresh();
    }

    public function form()
    {
        $this->textarea('reason', '备注')->rules('required');
    }

    public function html()
    {
        return "<a class='report-tong btn btn-sm btn-danger'><i class='fa fa-bullhorn'></i>补发通知</a>";
    }
}