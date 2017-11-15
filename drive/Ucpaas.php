<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2017/8/24
 * Time: 22:23
 */

namespace xing\sms\drive;


class Ucpaas implements \xing\sms\src\SmsDriveInterface
{

    public $config;

    private $base_url = 'https://api.ucpaas.com/';
    private $SoftVersion = '2014-06-30/';	# 版本号
    private $time;
    public $html;
    public $httpCode;

    public function config($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 发送文本验证码
     * @param $mobile
     * @param $code
     * @return bool
     */
    public function sendTextCode($mobile, $code)
    {
        if (empty($mobile) || empty($code)) return false;
        $data = array(
            'templateSMS' => array(
                'appId'		=> $this->config['templateTextCode']['appId'],	#  注意键名大小写
                'templateId'=> $this->config['templateTextCode']['tplid'],
                'to'		=> $mobile,
                'param'		=> $code,
            )
        );
        $data = $this->send($this->getUrl('/Messages/templateSMS'), $data);
        return !empty($data) ? $data['respCode'] == '000000' : false;
    }

    public function sendText($mobile, $content)
    {

        return true;
    }
    public function sendSoundCode($mobile, $code)
    {

        return true;
    }

    /**
     *  获取应用完整网址
     */
    private function getUrl($url) {
        if (empty($this->config)) throw new \Exception('没有配置');
        $this->time = date('YmdHis',time() + 7200);
        $url = $this->base_url.$this->SoftVersion.'Accounts/'.$this->config['accountSid'].$url;

        $url .=  '?sig='.strtoupper(md5($this->config['accountSid'].$this->config['Token'].$this->time));
        return $url;
    }

    public function getSendResource()
    {
        return ['resource' => $this->html, 'httpCode' => $this->httpCode];
    }

    /**
     * 执行发送
     * @param $url
     * @param array $data
     * @return mixed
     */
    private function send($url, $data = array()){

        $header = array(
            'Accept:application/json',
            'Content-Type:application/json;charset=utf-8',
            'Authorization:' . trim(base64_encode($this->config['accountSid'].':'.$this->time)),
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if(is_array($data)){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $this->html = curl_exec($ch);
        $this->httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);	# 返回状态码
        curl_close($ch);
        $r = json_decode($this->html,1);
        return is_array($r) ? $r['resp'] : $this->html;

    }
}