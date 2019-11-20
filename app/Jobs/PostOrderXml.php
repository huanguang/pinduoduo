<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 1:38
 */

namespace App\Jobs;

use App\Tools\CreateXml;
use App\Tools\HttpRequest;
use App\Tools\Rsa;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PostOrderXml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //推送异步通知
        $url = $this->data['url'];
        //获取商户公钥
        $merchant = DB::table('merchant')->where('id',$this->data['order']->merchant_id)->select('key_url','merchant','pass')->first();
        $path = $merchant->key_url;
        $cer = $path.$merchant->merchant.'.cer';
        $pfx = $path.$merchant->merchant.'.pfx';
        $pass = $merchant->pass;
        $newData = [
            'order_sn'=>$this->data['order_sn'],
            'msg'=>$this->data['msg'],
            'orderStatus'=>$this->data['orderStatus'],
            'payTime'=>$this->data['payTime'],
            'orderAmount'=>$this->data['orderAmount'],
        ];
        $time = time();
        $newData['sign'] = Rsa::encode($newData,$pass,$pfx);
        //post发送数据
        //Log::error($url);
//        if($this->data['order']->notify_time >= 60){
//            DB::table('order')->where('id',$this->data['order']->id)->update(['is_notice'=>3,'notify_time'=>60,'last_time'=>$time]);
//            return;
//        }
        $res = HttpRequest::postXml($url,CreateXml::create($newData));
//        if (Cache::has('order_'.$this->data['order_sn'])) {
//            Cache::increment('order_'.$this->data['order_sn'], 4);
//        }else{
//            $expiresAt = Carbon::now()->addMinutes(60);
//            Cache::put('order_'.$this->data['order_sn'], 4, $expiresAt);
//        }
        //Log::error($res);
        //记录推送通知的次数
        DB::table('order')->where('id',$this->data['order']->id)->increment('notice_num',1);
        if($res && $res == 200){
            //请求状态为200视为通知成功，通知成功后，两秒后再通知一次就不进行通知了,利用缓存记录推送的信息
            //Cache::forget('order_'.$this->data['order_sn']);
            DB::table('order')->where('id',$this->data['order']->id)->update(['is_notice'=>2]);
            //返回成功，通知完毕
            return;
        }
        if(Cache::get('order_'.$this->data['order_sn']) >= 60){
            //通知失败，完毕
            Cache::forget('order_'.$this->data['order_sn']);
            DB::table('order')->where('id',$this->data['order']->id)->update(['is_notice'=>3]);
            return;
        }
        //回调自己
        //$time = date('Y-m-d H:i:s',Cache::get('order_'.$this->data['order_sn']) + time());
        //\App\Jobs\PostOrderXml::dispatch($this->data);
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
        // 通知失败
        DB::table('order')->where('id',$this->data['order']->id)->update(['is_notice'=>3]);
    }
}