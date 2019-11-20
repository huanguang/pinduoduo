<?php

/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 16:38
 */
namespace App\Admin\Actions\Order;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Tools\Rsa;
use Illuminate\Support\Str;

class OrderQr extends RowAction
{
    public $name = '支付二维码';

    public function handle(Model $model, Request $request)
    {
        $path = base_path().$model->replicate()->key_url.'/';
        $zip_file = public_path('/').$model->replicate()->merchant.'.zip'; // 要下载的压缩包的名称
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFile($path.$model->replicate()->merchant.'.cer');
        $zip->addFile($path.$model->replicate()->merchant.'.pfx');
        $zip->close();
        $http = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $this->response()->success('文件压缩成功')->download($http.$_SERVER['HTTP_HOST'].'/'.$model->replicate()->merchant.'.zip');
    }
}