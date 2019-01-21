<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2019/1/19
 * Time: 13:33
 */

namespace xing\sms\drive;


use xing\helper\resource\HttpHelper;
use xing\sms\src\SmsDriveInterface;

class ManDao implements SmsDriveInterface
{

    public $config;
    public $sendUrl = 'http://sdk.entinfo.cn:8061/webservice.asmx/mdsmssend';
    public $templateCode = '您的验证码为：{$code}';
    public $result;

    public function config($config)
    {
        $this->config = $config;
        return $this;
    }


    public function sendText($mobile, $content)
    {

        $data = array(
            'sn' => $this->config['sn'], //提供的账号
            'pwd' => strtoupper(md5($this->config['sn'] . $this->config['pwd'])), //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
            'mobile' => $mobile, //手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
            'content' => htmlspecialchars($content), //短信内容
            //htmlspecialchars() 函数把一些预定义的字符转换为 HTML 实体。
            'ext' => '',
            'stime' => '', //定时时间 格式为2011-6-29 11:09:21
            'rrid' => '',//默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值
            'msgfmt'=>''
        );

        $result =$this->send($this->sendUrl,$data);

        $result = $this->result = trim(strip_tags($result));
        return $result > 0;
    }
    public function sendTextCode($mobile, $code)
    {
        return $this->sendText($mobile, str_replace('{$code}', $code, $this->templateCode));
    }
    public function sendSoundCode($mobile, $code)
    {

    }
    public function sendBatchText(array $mobiles, $content)
    {

        return $this->sendText(implode(',', $mobiles), $content);
    }

    private function send($url, $data)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        $data = http_build_query($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $lst = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl);
        return $lst;
    }
}