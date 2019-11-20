<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/14
 * Time: 17:17
 */

namespace App\Admin\Actions\Buyer;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Tools\Rsa;
use Illuminate\Support\Str;

class Generate extends RowAction
{
    public $name = '生成密钥';

    public function dialog()
    {
        $this->confirm('生成将覆盖之前的密钥，确定？');
    }
    public function handle(Model $model, Request $request)
    {
        $dn = array(
            "countryName" => 'zh', //所在国家名称
            "stateOrProvinceName" => 'GuangDong', //所在省份名称
            "localityName" => 'GuangZshou', //所在城市名称
            "organizationName" => 'LIUHUANGUANG',   //注册人姓名
            "organizationalUnitName" => 'KUYI', //组织名称
            "commonName" => 'KUYICOMPAMY', //公共名称
            "emailAddress" => $model->replicate()->merchant.'jkjghjg' //邮箱
        );
        $path = base_path()."/cert/".date('Y/m/d').'/'.$model->replicate()->merchant;
        $path2 = "/cert/".date('Y/m/d').'/'.$model->replicate()->merchant;
        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
        $privKeyPass = '111111'; //私钥密码
        $numberOfDays = 36500;     //有效时长
        $cerPath = "{$path}/{$model->replicate()->merchant}.cer"; //生成证书路径
        $pfxPath = "{$path}/{$model->replicate()->merchant}.pfx"; //密钥文件路径
        //生成证书
        $privKey = openssl_pkey_new(array(
            "private_key_type" => OPENSSL_KEYTYPE_EC,
            "curve_name" => 'prime256v1',
        ));
        $csr = openssl_csr_new($dn, $privKey);
        $sscert = openssl_csr_sign($csr, null, $privKey, $numberOfDays);
        openssl_x509_export_to_file($sscert, $cerPath); //导出证书到文件
        openssl_pkcs12_export_to_file($sscert, $pfxPath, $privKey, $privKeyPass); //生成密钥文件
        //生成app和key
        $data = [
            'secret_key'=>Str::random(32),
            'is_key'=>2,
            'key_url'=>$path2.'/',
        ];
        DB::table('merchant')->where('merchant',$model->replicate()->merchant)->update($data);
        return $this->response()->success('生成成功！')->refresh();
    }
}