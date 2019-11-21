<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/6/19
 * Time: 19:26
 */

namespace App\Admin\Controllers;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;

class OrderChartsController extends Controller
{
    use HasResourceActions;
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $Date1=strtotime("today"); /*格式 年-月-日 或 年-月-日 时:分:秒*/
        $Date2=strtotime("last month");
        $j=0;
        $str = '';
        $str2 = '';

        $arr = [];
        $arr2= [];
        for($i = $Date1; $i > $Date2; $i -= 86400)
        {
            //待付款
            $arr[date("Y-m-d",$i)]['qxCount'] = $qxCount =Order::where('status',0)->whereBetween('created_at',[date("Y-m-d",$i),date("Y-m-d",$i+86400)])->select('money')->sum('money');
            //待发货
            $arr[date("Y-m-d",$i)]['dzfCount'] = $dzfCount =Order::where('status',1)->whereBetween('created_at',[date("Y-m-d",$i),date("Y-m-d",$i+86400)])->select('money')->sum('money');
            //待收货
            $arr[date("Y-m-d",$i)]['dfhCount'] = $dfhCount =Order::where('status',2)->whereBetween('created_at',[date("Y-m-d",$i),date("Y-m-d",$i+86400)])->select('money')->sum('money');
            //待评价
            $arr[date("Y-m-d",$i)]['dshCount'] = $dshCount =Order::where('status',3)->whereBetween('created_at',[date("Y-m-d",$i),date("Y-m-d",$i+86400)])->select('money')->sum('money');
            //交易取消
            $arr[date("Y-m-d",$i)]['ywcCount'] = $ywcCount =Order::where('status',4)->whereBetween('created_at',[date("Y-m-d",$i),date("Y-m-d",$i+86400)])->select('money')->sum('money');
            //等待到账
            $arr[date("Y-m-d",$i)]['thzCount'] = $thzCount =Order::where('status',5)->whereBetween('created_at',[date("Y-m-d",$i),date("Y-m-d",$i+86400)])->select('money')->sum('money');
            //拼单成功，待发货
            $arr[date("Y-m-d",$i)]['ytkCount'] = $ytkCount =Order::where('status',6)->whereBetween('created_at',[date("Y-m-d",$i),date("Y-m-d",$i+86400)])->sum('money');
            $ThisDate=date("Y-m-d",$i);
            $str .= '\''.$ThisDate.'\',';
            if($i > strtotime("-7 day")){
                $str2 .= '\''.$ThisDate.'\',';
                $arr2[] = array_values($arr[date("Y-m-d",$i)]);
            }
        }
        $arr = json_encode($arr,true);
        $arr2 = json_encode($arr2,true);
        $str .= '\''.date("Y-m-d",$Date1).'\'';
        return $content
            ->header('订单管理')
            ->description('订单管理')
            ->body(new Box('订单图表', view('admin.charts.order',compact(['str','str2','arr','arr2']))));
    }
}