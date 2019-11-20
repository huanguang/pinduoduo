<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/17
 * Time: 23:56
 */

namespace App\Tools;


class Md5
{
    const appId = 'W4dCuK3OfztXFTiq';

    const secretKey = 'NHbRNVjp8X2ZRMLfPB4tG0h22Uz4mFpz';


    /**
     * @param array $data 需要加密的数据
     * @param string $appId 商户appId
     * @param string $secretKey 商户secretKey
     * @return string 返回的加密串
     */
    public static function encode($data,$appId,$secretKey) {
        return md5($appId.md5(self::strSort($data)).$secretKey);
    }

    /**
     * 验证签名
     * @param string $signMsg 需要验证的加密串
     * @param array $data 需要验证的数据
     * @param string $appId 商户appId
     * @param string $secretKey 商户secretKey
     * @return bool 返回验签结果，false/true
     */
    public static function decode($signMsg,$data,$appId,$secretKey){
        return md5($appId.md5(self::strSort($data)).$secretKey) === $signMsg;
    }


    /**
     * 数组按字典排序并返回排序后的字符串
     * @param array $data 需要排序的数组
     * @return bool|string 返回排序后字符串
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