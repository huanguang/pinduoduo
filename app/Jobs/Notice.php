<?php
namespace App\Jobs;

use App\Models\Buyer;
use App\Tools\AppPath;
use App\Tools\CreateXml;
use App\Tools\HttpRequest;
use App\Tools\Rsa;
use App\Tools\Url;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Models\Order;
use App\Jobs\PostOrderXml;
use Illuminate\Support\Facades\Log;
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/15
 * Time: 18:39
 */
class Notice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $order;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

         //延迟一分钟，获取订单状态，推送异步通知
//        if($this->order->status == 0 || $this->order->status == 4){
//            return;
//        }
        //获取订单状态

//        $data = ['appid' => 'W4dCuK3OfztXFTiq'];
//        $data['inside'] = 100;
//        $data['timestamp'] = time();
//        $data['order_sn'] = $this->order->pdd_order_sn;
//        $data['sign'] = Rsa::encode($data,AppPath::APP_RSA_PASS_PATH,AppPath::APP_RSA_PFX_PATH);
        //$dispatcher = app('Dingo\Api\Dispatcher');
        //$res = $dispatcher->json($data)->post('order/details');
          //  dump($res);die;
        $res = $this->getOrderDetails();
        if($res['status_code'] != 200){
            return;
        }
        //更新订单状态
        $this->order->status = $res['data']['orderStatus'];
        $this->order->api_status_str = $res['data']['chatStatusPrompt'];
//        if($res['data']['orderStatus'] != 0 || $res['data']['orderStatus'] != 4 ){
//            $this->order->pay_at = $res['data']['payTime'];
//        }
        $this->order->save();


        if($res['data']['orderStatus'] != 0){
            //订单交易成功，推送异步通知
            if($this->order->is_calculation == 1){
                //更新买家和卖家收入
                DB::transaction(function (){
                    //增加买家消费金额
                    DB::table('buyer')
                        ->where('user_id',$this->order->buyer_id)
                        ->increment('money',$this->order->money,['money'=>DB::raw('`today_money`+'.$this->order->money)]);
                    //店铺金额
                    DB::table('shop')
                        ->where('shop_id',$this->order->shop_id)
                        ->increment('money',$this->order->money,['money'=>DB::raw('`today_money`+'.$this->order->money)]);
                    DB::table('order')->where('id',$this->order->id)->update(['is_calculation'=>2]);
                });

            }
            $url = $this->order->notify_url;
            //生成xml数据
            $msg['order_sn'] = $this->order->request_order_sn;
            $msg['msg'] = $res['data']['orderStatus'];
            $msg['orderStatus'] = $res['data']['orderStatus'];
            $msg['payTime'] = $res['data']['payTime'];
            $msg['orderAmount'] = $res['data']['orderAmount'];
            $msg['url'] = $url;
            $msg['order'] = $this->order;
            Log::error($url);
            //延迟一分钟执行队列
            \App\Jobs\PostOrderXml::dispatch($msg);
            //PostOrderXml::dispatch($msg)->delay(now()->addMinutes(1));
        }
        echo 'ok';
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

    protected function getOrderDetails(){
        $order = $this->order;
        if($order){
            $user = Buyer::where('user_id',$order->buyer_id)->select('access_token')->first();
            if(isset($user->access_token)){
                $data = HttpRequest::get(sprintf(Url::$orderDetailsUrl,$order->pdd_order_sn),["Cookie: PDDAccessToken={$user->access_token}"]);
                $data = trimall($data);
                $data = get_between($data,'window.rawData=',';window.leo');
                //将截取的json格式字符串
                $data = json_decode($data,true);
                $newData = [
                    'payTime'=>$data['data']['payTime'],
                    'orderStatus'=>$data['data']['orderStatus'],
                    'chatStatusPrompt'=>$data['data']['chatStatusPrompt'],
                    'orderAmount'=>$data['data']['orderAmount'],
                ];
                return ['message'=>'请求成功','status_code'=>200,'data'=>$newData];
                //dump($data['data']['payStatus']);die;

//                    if (preg_match('/"chatStatusPrompt":"([^"]*)"/', $data, $matches)){
//                        return response()->json(['message'=>'请求失败','status_code'=>200,'data'=>$matches],200);
//                    }

                //判断订单状态
            }
        }
        return false;
    }
}