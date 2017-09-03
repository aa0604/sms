<?php
/**
 * 在框架下使用的接口
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2017/8/26
 * Time: 10:23
 */

namespace xing\sms\src;


interface SmsFrame
{
    /**
     * 生成验证码
     * @param int $len
     * @return string
     */
    public function createCode($len = 4);
    /**
     * 读取保存的验证码
     * @return mixed
     */
    public function getCode();

    /**
     * 保存验证码
     * @return bool
     */
    public function saveCode();
    /**
     * 检查验证码
     * @param $code
     * @return bool
     */
    public function checkCode($code);
}