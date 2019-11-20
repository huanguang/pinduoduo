<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 14:26
 */

namespace App\Tools;


class OrderType
{
    const zd = 1;

    const sd = 2;


    public static $status = [
        self::zd => '自动下单',
        self::sd => '手动下单',
    ];
}