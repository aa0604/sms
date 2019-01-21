<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/7/20
 * Time: 15:54
 */

namespace xing\sms\drive;

require_once dirname(__DIR__) . '/sdk/aliyun/vendor/autoload.php';
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;

// 加载区域结点配置
Config::load();

class Ali implements \xing\sms\src\SmsDriveInterface
{

    public $config;
    public $Ali;
    static $acsClient = null;

    public function config($config)
    {
        $this->config = $config;
        return $this;
    }

    public function getRequest($mobile)
    {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        //可选-启用https协议
        //$request->setProtocol("https");

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($mobile);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($this->config['signName'] ?? '');

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($this->config['codeTemplate'] ?? '');
        return $request;
    }
    /**
     * 发送文本验证码
     * @param $mobile
     * @param $code
     * @return bool
     */
    public function sendTextCode($mobile, $code)
    {

        $request = $this->getRequest($mobile);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "code"=> $code
        ), JSON_UNESCAPED_UNICODE));


        // 发起访问请求
        $acsResponse = $this->getAcsClient()->getAcsResponse($request);

        if ($acsResponse->Code != 'OK')
            throw new \Exception('短信接口调用返回错误信息：' .$acsResponse->Message . 'code:'.$acsResponse->Code);
        return true;
    }

    public function sendText($mobile, $content)
    {

        return true;
    }

    public function sendSoundCode($mobile, $code)
    {

        return true;
    }

    public function sendBatchText(array $mobiles, $content)
    {
        return $this->sendText(implode(',', $mobiles), $content);
    }

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = $this->config['accessKeyID'] ?? ''; // AccessKeyId

        $accessKeySecret = $this->config['accessKeySecret'] ?? ''; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

}