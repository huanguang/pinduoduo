<?php

namespace App\Admin\Actions\Merchant;

use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantStatus extends BatchAction
{
    protected $selector = '.merchant-status';

    public function handle(Collection $collection, Request $request)
    {
        $reason = $request->get('reason');
        foreach ($collection as $model) {
            $model->is_enable = 0;
            $model->save();
            //记录操作日志
            DB::table('merchant_operation_log')
                ->insert([
                    'admin_id'=>Admin::user()->id,
                    'merchant_id'=>$model->id,
                    'content'=>$reason,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
        }
        return $this->response()->success('关闭成功')->refresh();
    }

    public function form()
    {
        $this->textarea('reason', '备注')->rules('required');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-danger merchant-status"><i class='fa fa-bullhorn'></i>批量关闭</a>
HTML;
    }
}