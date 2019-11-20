<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/16
 * Time: 0:14
 */

namespace App\Tools;


class CreateXml
{
    /**
     * 创建xml
     *
     */
    public static function create($data){
        if(!$data){
            return false;
        }
        $xml = "<?xml version='1.0' encoding='UTF-8'?><xml>";
        foreach ($data as $key=>$val)
        {
            if(is_array($val)){
                $xml.="<".$key.">";
                foreach ($val as $k=>$v){
                    if (is_numeric($v)){
                        $xml.="<".$k.">".$v."</".$k.">";
                    }else{
                        $xml.="<".$k."><![CDATA[".$v."]]></".$k.">";
                    }
                }
                $xml.="</".$key.">";
            }else{
                if (is_numeric($val)){
                    $xml.="<".$key.">".$val."</".$key.">";
                }else{
                    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
                }
            }
        }
        $xml.="</xml>";
        return $xml;
    }
}