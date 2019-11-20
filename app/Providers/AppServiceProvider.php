<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //接管异常
        \API::error(function (\Illuminate\Validation\ValidationException $exception){
            $data =$exception->validator->getMessageBag();
            $msg = collect($data)->first();
            if(is_array($msg)){
                $msg = $msg[0];
            }
            return response()->json(['message'=>$msg,'status_code'=>422], 200);
        });
        \API::error(function (\Dingo\Api\Exception\ValidationHttpException $exception){
            $errors = $exception->getErrors();
            return response()->json(['message'=>$errors->first(),'status_code'=>422], 200);
        });


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * 队列失败处理
         */
//        Queue::failing(function (JobFailed $event) {
//            // $event->connectionName
//            // $event->job
//            // $event->exception
//        });
//
//        Queue::before(function (JobProcessing $event) {
//            // $event->connectionName
//            // $event->job
//            // $event->job->payload()
//        });
//
//        Queue::after(function (JobProcessed $event) {
//            // $event->connectionName
//            // $event->job
//            // $event->job->payload()
//        });
    }
}
