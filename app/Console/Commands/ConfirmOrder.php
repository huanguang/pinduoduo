<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 17:15
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
class ConfirmOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:confirmOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '确认收货';

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
        \App\Jobs\ConfirmOrder::dispatch();
        echo '执行成功！';
    }
}