<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2017/8/24
 * Time: 21:52
 */

namespace xing\sms\src;


interface SmsInterface
{
    public function config($config);
    public function sendText($mobile, $content);
    public function sendTextCode($mobile, $code);
    public function sendSoundCode($mobile, $code);

}