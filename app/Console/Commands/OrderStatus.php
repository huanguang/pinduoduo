<?php

/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 17:12
 */
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Order;
class OrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:updateOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新订单状态';

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
        $list = Order::whereOr('status',0)->whereOr('status',1)->whereOr('status',2)->select('id');
        if($list){
            foreach ($list as $item){
                \App\Jobs\Notice::dispatch(Order::find($item->id));
            }
        }

        echo '执行成功！';
    }
}