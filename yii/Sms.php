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

class Sms extends Component implements \xing\sms\src\SmsInterface
{

    public $ucpaas;

    private $config;
    private $driveName;
    private $verifyCode;
    private $expireTime = 600;
    private $mobile;

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


    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return \xing\sms\drive\Ucpaas
     */
    public function getInstance()
    {
        return SmsFactory::getInstance($this->driveName)->config($this->config);
    }

    public function config($config){}

    public function sendText($content)
    {
        return $this->getInstance()->sendText($this->mobile, $content);
    }

    public function sendTextCode($code)
    {
        return $this->getInstance()->sendTextCode($this->mobile, $code);
    }

    public function sendSoundCode($code)
    {
        return $this->getInstance()->sendSoundCode($this->mobile, $code);
    }

    public function createCode($len = 4)
    {
        for ($i = 1; $i <= $len; $i++) $this->verifyCode .= (string) rand(0,9);
        $this->saveCode();
        return $this->verifyCode;
    }

    public function getCode()
    {
        $key = 'smsCode:'. $this->mobile;
        return \Yii::$app->cache->get($key);
    }

    public function saveCode()
    {
        $key = 'smsCode:'. $this->mobile;
        return \Yii::$app->cache->set($key, $this->verifyCode, $this->expireTime);
    }

    public function checkCode($code)
    {
        return $this->getCode() == $code;
    }

}