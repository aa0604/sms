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

    private $base_url = 'https://open.ucpaas.com/';
    private $SoftVersion = '2017-06-30/';	# 版本号
    private $time;
    public $html;
    public $httpCode;
    public $errorMessage = '';
    public $result;

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
        $data = $this->send($this->getUrl('/ol/sms/sendsms'), $data);
        return $data['respCode'] == '000000';
    }

    public function sendText($mobile, $content)
    {

        return true;
    }

    /**
     * 发送模板消息
     * @param $mobile
     * @param $templateConfig
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function sendTemplateSms($mobile, $templateConfig, array $params = [])
    {
        if (empty($mobile)) throw new \Exception('手机号为空');
        if (empty($templateConfig)) throw new \Exception('模板配置为空');

        $data = [
            'templateSMS' => [
                'appId'		=> $templateConfig['appId'],
                'templateId'=> $templateConfig['tplid'],
                'to'		=> $mobile,
                'param'		=> implode(',', $params),
            ]
        ];
        $data = $this->result = $this->send($this->getUrl('/Messages/templateSMS'), $data);
        return $data['respCode'] == '000000';
    }

    public function sendSoundCode($mobile, $code)
    {

        $this->base_url = 'http://message.ucpaas.com/';
        $url = $this->getUrl('/Calls/voiceVerify');

        $post = [
            'voiceVerify' => [
                'appId'		=> $this->config['soundAppId'],
                'captchaCode'=> $code,
                'to'		=> $mobile,
                'playTimes' => '3',
//                'displayNum'=> 13667898805,			//显示号码
            ]
        ];

        $data = $this->send($url, $post);
        return $data['respCode'] == '000000';
    }

    public function sendBatchText(array $mobiles, $content)
    {
        return $this->sendText(implode(',', $mobiles), $content);
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     *  获取应用完整网址
     */
    private function getUrl($url) {
        if (empty($this->config)) throw new \Exception('没有配置');
        $this->time = date('YmdHis');
        $url = $this->base_url.$this->SoftVersion.'Accounts/'.$this->config['accountSid'].$url;

        $url .=  '?sig='.strtoupper(md5($this->config['accountSid'].$this->config['token'].$this->time));
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
        $data = is_array($r) ? $r['resp'] : $this->html;

        if (!isset($data['respCode'])) throw new \Exception('访问短信接口失败：无返回状态码');
        if ($data['respCode'] == 105147) throw new \Exception('同一手机号今天发送次数达到上限');
        if ($data['respCode'] != '000000') throw new \Exception('短信发送失败：' . $data['respCode']);
        if ($data['respCode'] == '100015') throw new \Exception('请输入标准的国内手机号码');
        if ($data['respCode'] == '101106') throw new \Exception('time过期');
        return $data;
    }
}