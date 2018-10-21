<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2017/8/24
 * Time: 21:55
 *
// 正常用法：
SmsFactory::getSms('ucpaas')->config([])->sendTextCode(13667898888,1234);
// yii2 用法
Sms::sendTextCode(123,123);
 */

namespace xing\sms\src;



class SmsFactory
{
    private static $drive = [
        'ucpaas' => '\xing\sms\drive\Ucpaas',
        'Ali' => '\xing\sms\drive\Ali',
    ];
    /**
     * 返回单例
     * @param $payInstanceName
     * @return \xing\sms\drive\Ucpaas
     */
    public static function getInstance($instanceName)
    {
        static $class;
        if (isset($class[$instanceName])) return $class[$instanceName];
        return $class[$instanceName] = new self::$drive[$instanceName]();
    }
}