<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 21:14
 */

namespace App\Admin\Actions\Order;


use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class Reason extends BatchAction
{
    protected $selector = '.report-posts';

    public function handle(Collection $collection, Request $request)
    {
        $reason = $request->get('reason');
        foreach ($collection as $model) {
            $model->reason = $reason;
            $model->save();
        }

        return $this->response()->success('备注已提交！')->refresh();
    }

    public function form()
    {
        $this->textarea('reason', '说明')->rules('required');
    }

    public function html()
    {
        return "<a class='report-posts btn btn-sm btn-danger'><i class='fa fa-info-circle'></i>备注</a>";
    }
}