<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Queue;

class OrderStatusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::after(function ($connection, $job, $data) {
            //
        });
        //后面2个参数，暂时没有测试出来，我发现后面都是空，所以应该写成
        Queue::after(function ($callback) {
            //
        });
    }
}
