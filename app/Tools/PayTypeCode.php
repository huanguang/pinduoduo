<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 14:14
 */

namespace App\Tools;


class PayTypeCode
{
    const zfb = 1;

    const wx = 2;

    const df = 3;

    public static $status = [
        self::zfb => '支付宝',
        self::wx => '微信',
        self::df => '代付',
    ];
}