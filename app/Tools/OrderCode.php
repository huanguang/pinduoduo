<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/11/13
 * Time: 11:07
 */

namespace App\Tools;


class OrderCode
{
    const UNPAY = 0;

    const UNSHOPING = 1;

    const UNRECEIVED = 2;

    const UNRATED = 3;

    const CANCELED = 4;

    const UNWAIT = 5;
    const FAHUO = 6;
    public static $status = [
        self::UNPAY => '待付款',
        self::UNWAIT => '等待到账',
        self::UNSHOPING => '待发货',
        self::UNRECEIVED => '待收货',
        self::UNRATED => '待评价',
        self::CANCELED => '交易已取消',
        self::FAHUO => '拼单成功，待发货',


    ];

    public static $api_status = [
        self::UNPAY => 'unpaidV2',
        self::UNSHOPING => 'unshipping',
        self::UNRECEIVED => 'unreceived',
        self::UNRATED => 'unrated',
        self::CANCELED => 'canceled',
        self::UNWAIT => 'unwait',
        self::FAHUO => 'fahuo',
    ];

}