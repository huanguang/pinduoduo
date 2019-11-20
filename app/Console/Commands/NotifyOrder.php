<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/19
 * Time: 19:30
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
class NotifyOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:notifyOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '异步推送订单状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //队列更新订单状态
        $s_time2 = date('Y-m-d H:i:s',strtotime(date('Y-m-d')) - (60*60));
        $e_time2 = date('Y-m-d H:i:s',time());
        $list = Order::where('is_notice',3)->whereOr('status',1)->where('notice_num','<',10)->whereOr('status',2)->whereOr('status',3)->whereOr('status',5)->whereOr('status',6)->whereBetween('created_at',[$s_time2,$e_time2])->get();
        if($list){
            foreach ($list as $item){
                $url = $item->notify_url;
                $msg['order_sn'] = $item->request_order_sn;
                $msg['msg'] = $item->api_status_str;
                $msg['orderStatus'] = $item->status;
                $msg['payTime'] = $item->pay_at;
                $msg['orderAmount'] = $item->order_amount;
                $msg['url'] = $url;
                $msg['order'] = $item;
                //延迟一分钟执行队列
                \App\Jobs\PostOrderXml::dispatch($msg);
            }
        }
        echo '执行成功！';
    }
}