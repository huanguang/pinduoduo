<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/14
 * Time: 18:23
 */

namespace App\Tools;


class Rsa
{
    //const PASS = '111111';

    //const CER = '/cert/123456.cer';

    //const PFX = '/cert/123456.pfx';

    /**
     * 加密
     * @param string $data 需要加密的字符串
     */
    public static function encode($data,$pass,$pfx) {
        $pfxPath = base_path().$pfx; //密钥文件路径
        $cer_key =file_get_contents($pfxPath); //获取密钥内容
        openssl_pkcs12_read($cer_key, $certs, $pass);
        openssl_sign(self::strSort($data), $signMsg, $certs['pkey'],OPENSSL_ALGO_SHA1);//注册生成加密信息
        $signMsg =base64_encode($signMsg); //base64转码加密信息
        return $signMsg;
    }

    /**
     * 验证签名
     * @param string $sign 需要验证的签名
     * @param string $data 需要验证的签名的数据
     */
    public static function decode($signMsg,$data,$cer){
        $cerPath = base_path().$cer; //密钥文件路径
        $cer_key =file_get_contents($cerPath); //获取证书内容
        $unSignMsg=base64_decode($signMsg);//base64解码加密信息
        $cer =openssl_x509_read($cer_key); //读取公钥
        return openssl_verify(self::strSort($data), $unSignMsg, $cer);
    }

    /**
     * 数组按字典排序并返回加密后的字符串
     * @param array $data 需要排序的数组
     */
    private static function strSort($data = []){
        if(empty($data)){
           return false;
        }
        ksort($data);
        $str = '';
        foreach ($data as $key=>$value){
            if($value){
                $str .= "{$key}={$value}&";
            }
        }
        return $str;
    }
}