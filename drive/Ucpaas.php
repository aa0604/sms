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
    public $errorMessage = '';

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

        $data = array(
            'templateSMS' => array(
                'appId'		=> $templateConfig['appId'],
                'templateId'=> $templateConfig['tplid'],
                'to'		=> $mobile,
                'param'		=> implode(',', $params),
            )
        );
        $data = $this->send($this->getUrl('/Messages/templateSMS'), $data);
        return $data['respCode'] == '000000';
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
        $data = is_array($r) ? $r['resp'] : $this->html;

        if (!isset($data['respCode'])) {
            $this->errorMessage = '访问短信接口失败：无返回状态码';
            return false;
        }
        if ($data['respCode'] == 105147) {
            $this->errorMessage = '同一手机号今天发送次数达到上限';
            return false;
        }
        if ($data['respCode'] != '000000') {
            $this->errorMessage = '短信发送失败：' . $data['respCode'];
            return false;
        }
        if ($data['respCode'] == '100015') {
            $this->errorMessage = '请输入标准的国内手机号码';
            return false;
        }
        return $data;
    }
}