<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/12
 * Time: 17:58
 */
/*
 * php截取指定两个字符之间字符串
 * */
function get_between($input, $start, $end) {
    $substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
    return $substr;
}

function trimall($str){
    $qian=array(" ","　","\t","\n","\r");
    return str_replace($qian, '', $str);
}