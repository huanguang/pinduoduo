<?php
namespace App\Admin\Actions\Goods;
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/17
 * Time: 2:06
 */
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsStatusXia extends BatchAction
{
    protected $selector = '.goods-xia-status';

    public function handle(Collection $collection, Request $request)
    {
        $reason = $request->get('reason');
        foreach ($collection as $model) {
            $model->is_enable = 0;
            $model->save();
            //记录操作日志
            DB::table('goods_operation_log')
                ->insert([
                    'admin_id'=>Admin::user()->id,
                    'goods_id'=>$model->id,
                    'content'=>$reason,
                    'type'=>2,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
        }
        return $this->response()->success('下架成功')->refresh();
    }

    public function form()
    {
        $this->textarea('reason', '备注')->rules('required');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-danger goods-xia-status"><i class='fa fa-bullhorn'></i>批量下架</a>
HTML;
    }
}