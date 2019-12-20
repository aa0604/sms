### 介绍
短信支持阿里大鱼、云之讯、漫道

云之讯支持语言验证码

注：阿里大鱼语音验证码，语音通知要购买号码，并且个人独资企业还没资格申请，估计是要股份有限公司才能申请。

## 安装
```php
composer require xing.chen/sms dev-master
```

# 目录
* [特点](#特点)
* [注意事项](#注意事项)
* [服务商驱动名及配置](#服务商驱动名及配置)
* [无框架使用示例](#无框架使用示例)
* [yii2配置和使用示例](#yii2配置和使用示例)
* [首信易](#首信易)

#### 特点
1、可不依赖框架运行

2、支持yii2框架

3、工厂模式开发，扩展性强，随时改变SMS驱动

4、全部使用interface接口规范开发

### 注意事项
1、如果你使用yii2驱动，需要开启配置cache

2、目前只开发了短信验证码发送，以后有需要发送其他模板短信后再开发

### 使用示例


```php
<?php
// 独立使用
$sms = \xing\sms\src\SmsFactory::getInstance('Ali或Ucpaas或ManDao')
->config($config)
;
//  发送验证码
$sms->sendTextCode('手机号', '验证码');
// 发送语音验证码（仅云之讯支持，阿里大鱼一般人用不了）
$sms->sendSoundCode('手机号', '验证码');
//  发送自定义内容短信（仅漫道支持）
$sms->sendText('手机号', '内容');

// 通过YII使用
$yiiSms = Yii::$app->sms->setMobile('手机号');
// 创建验证码
$code = $yiiSms->createCode();
//  发送验证码
$yiiSms->sendTextCode($code);
// 发送语音验证码（仅云之讯支持，阿里大鱼一般人用不了）
$yiiSms->sendSoundCode($code);
// 检查验证码：
if (!$yiiSms->checkCode($mobileCode)) throw new \Exception('验证码输入错误');
// 清除使用过的验证码：
$yiiSms->clearCode();
```
# YII配置
```php
'components' => [
    'sms' => [
            'class' => 'xing\sms\yii\Sms',
            'driveName' => 'Ali/Ucpaas/ManDao', // 阿里大鱼/云之讯/漫道
            'config' => $config, // 详细配置（见下面）
        ]
    ];

```
# 各详细配置
注意驱动名称需要区分大小写
阿里云配置： Ali
```php
<?php
$config = [
    'accessKeyID' => 'accountSid',
    'accessKeySecret' => 'accessKeySecret',
    // 必填，设置中文签名名称，应严格按"签名名称"填写，请参考: （短信->国内消息）https://dysms.console.aliyun.com/dysms.htm?spm=5176.2020520101.aliyun_sidebar.10.3b9c4df5bLOmra#/domestic/text/sign
    'signName' => '中文或英文签名',
    // 请自行根据业务动态设置模板id或写死在配置
    'codeTemplate' => '短信模板id',
];
```
云之讯配置：Ucpaas
```php
<?php
$config = [
    'accountSid' => 'accountSid',
    'token' => 'Token',
    // 请自行根据业务动态设置模板id或写死在配置
    'templateTextCode' => [
      'tplid' => '短信模板id',
      'appId' => '应用id',
    ]
];
```

漫道配置：ManDao
```php
<?php
$config = [
    'sn' => 'sn',
    'pwd' => 'pwd',
];
```
