### 介绍
短信驱动 目前支持云之讯
注：目前支持发送验证码和模板验证码，其中yii等框架支持缓存验证码及验证验证码是否正确，清除验证码等功能

#### 特点
1、支持yii2框架Component配置使用，使用缓存验证码，并内置验证方法
2、支持不嵌入框架直接使用
3、工厂模式开发，扩展性强，随时改变SMS驱动
4、全部使用interface接口规范开发

### 要求
1、如果你使用yii2驱动，需要开启配置cache

### 阿里云使用示例
```php
<?php

?>
```


```php
<?php
\xing\sms\drive::config(['accountSid' => 'accountSid',
                                        'Token' => 'Token',
                                        'templateTextCode' => [
                                            'tplid' => '短信模板id',
                                            'appId' => '应用id',
                                        ]])->sendTextCode('手机号', '验证码');
```

### yii2配置和使用示例
```php
<?php
'components' => [
    'sms' => [
            'class' => 'xing\sms\yii\Sms',
            'ucpaas' => [
                'accountSid' => 'accountSid',
                'Token' => 'Token',
                'templateTextCode' => [
                    'tplid' => '短信模板id',
                    'appId' => '应用id',
                ],
            ],
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