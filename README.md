### 介绍
短信驱动 目前支持云之讯
注：目前支持发送验证码和模板验证码，其中yii等框架支持缓存验证码及验证验证码是否正确，清除验证码等功能

# 目录
* [特点](#特点)
* [注意事项](#注意事项)
* [服务商驱动名及配置](#服务商驱动名及配置)
* [无框架使用示例](#无框架使用示例)
* [yii2配置和使用示例](#yii2配置和使用示例)
* [首信易](#首信易)

#### 特点
1、支持yii2框架Component配置使用，使用缓存验证码，并内置验证方法
2、支持不嵌入框架直接使用
3、工厂模式开发，扩展性强，随时改变SMS驱动
4、全部使用interface接口规范开发

### 注意事项
1、如果你使用yii2驱动，需要开启配置cache
2、目前只开发了短信验证码发送，以后有需要发送其他模板短信后再开发


### 服务商驱动名及配置
注意驱动名称需要区分大小写
阿里云： Ali
```php
<?php
$config = [
    'accessKeyID' => 'accountSid',
    'accessKeySecret' => 'accessKeySecret',
    // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    'signName' => '签名',
    // 请自行根据业务动态设置模板id或写死在配置
    'codeTemplate' => '短信模板id',
];
```
云之讯：Ucpaas
```php
<?php
$config = [
    'accountSid' => 'accountSid',
    'token' => 'Token',
    // 请自行根据业务动态设置模板id或写死在配置
    'templateTextCode' => [
      'tplid' => '短信模板id',
      'appId' => '应用id',
]];
```

漫道：ManDao
```php
<?php
$config = [
    'sn' => 'sn',
    'pwd' => 'pwd',
];
```
### 无框架使用示例


```php
<?php
//  发送验证码
\xing\sms\src\SmsFactory::getInstance('服务商驱动名')->config(/*服务商驱动配置*/)->sendTextCode('手机号', '验证码');
//  发送模板短信
\xing\sms\src\SmsFactory::getInstance('服务商驱动名')->config(/*服务商驱动配置*/)->sendText('手机号', '内容或模板id');
```

### yii2配置和使用示例
需要相应的服务商驱动和配置可以随意切换
```php
<?php
'components' => [
    'sms' => [
            'class' => 'xing\sms\yii\Sms',
            'driveName' => '服务商驱动名',
            'config' => [], // 服务商驱动配置
        ]
    ];

// 以下方法要先：设置手机号码
$sms = Yii::$app->sms->setMobile('手机号');

// 创建验证码
$code = $sms->createCode();

// 发送验证码
$r = $sms->sendTextCode($code);

exit($r ? '短信发送成功' : '短信发送失败');

// 发送模板短信
$sms->sendTemplateSms(['tplid' => '短信模板id','appId' => '应用id',], ['变量参数1，无则删除', '变量参数2，无则删除']);

// 检查验证码：
if (!Yii::$app->sms->setMobile('手机号')->checkCode($mobileCode)) 
    throw new \Exception('手机验证码错误');
// 清除缓存的验证码：
Yii::$app->sms->setMobile('手机号')->clearCode();
```