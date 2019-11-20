<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Goodss;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Shop;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('数据看板')
            ->description('数据看板')
            //->row(Dashboard::title())
            ->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $now_date = date('Y-m-d',time());
                    $day_date = date('Y-m-d',strtotime(date('Y-m-d')) - (86400));
                    $week_date = date('Y-m-d',strtotime(date('Y-m-d')) - (86400*7));
                    $s_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d 00:00:00')) - (86400*1));
                    $e_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d 00:00:00')));
                    $s_time2 = date('Y-m-d H:i:s',strtotime(date('Y-m-d')) - (86400*7));
                    $e_time2 = date('Y-m-d H:i:s',time());
                    $zuo = Order::whereBetween('created_at',[$s_time,$e_time])->count('id');
                    $qi = Order::whereBetween('created_at',[$s_time2,$e_time2])->count('id');
                    $box = new Box('订单数据','<div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>'.Order::count('id').'</h3>
                    
                            <p>订单总数</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="/admin/order" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>'.$zuo.'</h3>
                    
                            <p>昨日新增</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="/admin/order?&created_at%5Bstart%5D='.$day_date.'+00%3A00%3A00&created_at%5Bend%5D='.$day_date.'+23%3A59%3A59" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>'.$qi.'</h3>
                    
                            <p>近7日新增</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="/admin/order?&created_at%5Bstart%5D='.$week_date.'+00%3A00%3A00&created_at%5Bend%5D='.$now_date.'+23%3A59%3A59" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>');
                    //$box->removable();
                    $box->collapsable();
                    $box->style('info');
                    $box->solid();
                    $column->append($box);

                });
                $row->column(3, function (Column $column) {
                    $product_total = Goodss::count();
                    $product_sale = Goodss::where('is_enable',1)->count();
                    $product_review = Goodss::where('is_enable',0)->count();
                    $box = new Box('商品数据','<div class="small-box bg-green">
                        <div class="inner">
                            <h3>'.$product_total.'</h3>
                    
                            <p>商品总数</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-shopping-cart"></i>
                        </div>
                        <a href="/admin/goods/index" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-green">
                        <div class="inner">
                            <h3>'.$product_sale.'</h3>
                    
                            <p>上架数量</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-shopping-cart"></i>
                        </div>
                        <a href="/admin/goods/index?&status=1&review_status=1" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-green">
                        <div class="inner">
                            <h3>'.$product_review.'</h3>
                    
                            <p>下架数量</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-shopping-cart"></i>
                        </div>
                        <a href="/admin/goods/index?&review_status=0" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>');
                    //$box->removable();
                    $box->collapsable();
                    $box->style('success');
                    $box->solid();
                    $column->append($box);

                });
                $row->column(3, function (Column $column) {
                    $now_date = date('Y-m-d',time());
                    $week_date = date('Y-m-d',strtotime(date('Y-m-d')) - (86400*7));
                    $s_time2 = date('Y-m-d H:i:s',strtotime(date('Y-m-d')) - (86400*7));
                    $e_time2 = date('Y-m-d H:i:s',time());
                    $order_total = Order::where('status','>',0)->count();
                    $order_week = Order::where('status','>',0)->whereBetween('created_at',[$s_time2,$e_time2])->count();
                    $order_back = Order::where('status',3)->count();
                    $box = new Box('订单数据','<div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>'.$order_total.'</h3>
                    
                            <p>订单总数</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <a href="/admin/order" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>'.$order_week.'</h3>
                    
                            <p>周订单数</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <a href="/admin/order?&created_at%5Bstart%5D='.$week_date.'+00%3A00%3A00&created_at%5Bend%5D='.$now_date.'+23%3A59%3A59" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>'.$order_back.'</h3>
                    
                            <p>待收货订单数量</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <a href="/admin/order?&616f3545e14c181e4523e15f3e94a15d=2" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>');
                    //$box->removable();
                    $box->collapsable();
                    $box->style('warning');
                    $box->solid();
                    $column->append($box);

                });
                $row->column(3, function (Column $column) {
                    $now_date = date('Y-m-d',time());
                    $week_date = date('Y-m-d',strtotime(date('Y-m-d')) - (86400*7));
                    $s_time2 = date('Y-m-d H:i:s',strtotime(date('Y-m-d')) - (86400*7));
                    $e_time2 = date('Y-m-d H:i:s',time());
                    $supplier_total = Merchant::count();
                    $supplier_week = Merchant::whereBetween('created_at',[$s_time2,$e_time2])->count();
                    $supplier_disable = Merchant::where('is_enable',0)->count();
                    $box = new Box('商户数据','<div class="small-box bg-red">
                        <div class="inner">
                            <h3>'.$supplier_total.'</h3>
                    
                            <p>商户总数</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-file"></i>
                        </div>
                        <a href="/admin/merchant" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-red">
                        <div class="inner">
                            <h3>'.$supplier_week.'</h3>
                    
                            <p>周新增数</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-file"></i>
                        </div>
                        <a href="/admin/merchant?&created_at%5Bstart%5D='.$week_date.'+00%3A00%3A00&created_at%5Bend%5D='.$now_date.'+23%3A59%3A59" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div><div class="small-box bg-red">
                        <div class="inner">
                            <h3>'.$supplier_disable.'</h3>
                    
                            <p>已停止商户</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-file"></i>
                        </div>
                        <a href="/admin/merchant?&status=0" class="small-box-footer">
                            详情&nbsp;
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>');
                    //$box->removable();
                    $box->collapsable();
                    $box->style('danger');
                    $box->solid();
                    $column->append($box);

                });
            });
    }
}
