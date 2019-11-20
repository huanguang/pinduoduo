<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 17:20
 */

namespace App\Jobs;

use App\Models\Buyer;
use App\Tools\HttpRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Order;
class ConfirmOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $Minute;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($Minute = 0)
    {
        $this->Minute = $Minute;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //确认收货订单
        if($this->Minute > 0){
            //获取周期内的订单确认收货，分钟计算
            $time = $this->Minute * 60;
            $s_time = date('Y-m-d H:i:s',time()-$time);
            $e_time = date('Y-m-d H:i:s');
            $order = Order::whereBetween('created_at',[$s_time,$e_time])->select('pdd_order_sn','buyer_id')->select();
        }else{
            $order = Order::where('status',2)->select('pdd_order_sn','buyer_id')->select();
        }

        if($order){
            foreach ($order as $item){
                $Buyer = Buyer::where('user_id',$item->buyer_id)->select('access_token')->first();
                $headers = [
                    'AccessToken:'.$Buyer->access_token,
                    'Content-Type:application/json;charset=UTF-8',
                ];
                //确认收货
                echo htmlspecialchars(HttpRequest::post("https://mobile.yangkeduo.com/proxy/api/order/{$item->pdd_order_sn}/received?pdduid={$item->access_token}", [], $headers));
            }
        }
        //echo 'ok';
    }

    /**
     * 任务失败的处理过程
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        // 给用户发送任务失败的通知，等等……
    }
}