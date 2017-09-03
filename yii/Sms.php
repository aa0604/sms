<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2017/8/26
 * Time: 7:52
 */

namespace xing\sms\yii;

use yii\base\Component;
use xing\sms\src\SmsFactory;

class Sms extends Component implements \xing\sms\src\SmsInterface, \xing\sms\src\SmsFrame
{

    public $ucpaas;

    private $config;
    private $driveName;
    private $verifyCode;
    private $expireTime = 600;

    public function init()
    {
        parent::init();
        foreach ($this as $k => $v) {
            $this->config = $v;
            $this->driveName = $k;
            isset($v['expireTime']) && $this->expireTime = $v['expireTime'];
            break;
        }
    }

    /**
     * @return \xing\sms\drive\Ucpaas
     */
    public function getSms()
    {
        return SmsFactory::getSms($this->driveName)->config($this->config);
    }

    public function config($config){}

    public function sendText($mobile, $content)
    {
        return $this->getSms()->sendText($mobile, $content);
    }

    public function sendTextCode($mobile, $code)
    {
        return $this->getSms()->sendTextCode($mobile, $code);
    }

    public function sendSoundCode($mobile, $code)
    {
        return $this->getSms()->sendSoundCode($mobile, $code);
    }

    public function createCode($len = 4)
    {
        for ($i = 1; $i <= $len; $i++) $this->verifyCode .= (string) rand(0,9);
        $this->saveCode();
        return $this->verifyCode;
    }

    public function getCode()
    {
        $key = 'smsCode:'. \Yii::$app->request->userIP;
        return \Yii::$app->cache->get($key);
    }

    public function saveCode()
    {
        $key = 'smsCode:'. \Yii::$app->request->userIP;
        return \Yii::$app->cache->set($key, $this->verifyCode, $this->expireTime);
    }

    public function checkCode($code)
    {
        return $this->getCode() == $code;
    }

}