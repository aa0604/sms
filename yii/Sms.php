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

    private $key = 'smsCode:';

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


    /**
     * 发送模板消息
     * @param $templateConfig
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function sendTemplateSms($templateConfig, array $params = [])
    {
        if (empty($this->mobile)) throw new \Exception('手机号为空');
        if (empty($templateConfig)) throw new \Exception('模板配置为空');

        return $this->getInstance()->sendTemplateSms($this->mobile, $templateConfig, $params);
        return true;
    }
    public function getCode()
    {
        return \Yii::$app->cache->get($this->key());
    }

    public function saveCode()
    {
        $key = $this->key();
        return \Yii::$app->cache->set($key, $this->verifyCode, $this->expireTime);
    }

    public function clearCode()
    {
        return \Yii::$app->cache->delete($this->key());
    }

    private function key()
    {
        return $this->key. $this->mobile;
    }

    public function checkCode($code)
    {
        return $this->getCode() == $code;
    }

}