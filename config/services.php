<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sms' => [
        // 当前使用的短信公司：smsbao / aliyun
        'default' => env('SMS_DRIVER', 'smsbao'),

        'smsbao' => [
            'username' => env('SMSBAO_USERNAME'),
            'password' => env('SMSBAO_PASSWORD'),
            'sign'     => env('SMSBAO_SIGN'),
            // 短信宝为内容模板：{xxx} 为占位符，发送时替换
            'templates' => [
                'code' => '您的验证码是{code}，5分钟内有效，请勿泄露。',
                // 后续通知模板在此追加，如 'notice' => '...',
            ],
        ],

        'aliyun' => [
            'access_key_id'     => env('ALIYUN_SMS_ACCESS_KEY_ID'),
            'access_key_secret' => env('ALIYUN_SMS_ACCESS_KEY_SECRET'),
            'sign_name'         => env('ALIYUN_SMS_SIGN_NAME'),
            // 阿里云为模板 code，params 通过 TemplateParam 传递
            'templates' => [
                'code' => 'SMS_000000', // 请替换为实际验证码模板 code
                // 后续通知模板在此追加，如 'notice' => 'SMS_000001',
            ],
        ],
    ],

    'wx' => [
        // 微信小程序
        'mini' => [
            'appid'  => env('WX_MINI_APPID'),
            'secret' => env('WX_MINI_SECRET'),
        ],
        // 微信公众号（H5 网页授权）
        'mp' => [
            'appid'  => env('WX_MP_APPID'),
            'secret' => env('WX_MP_SECRET'),
        ],
        // 微信 APP（开放平台移动应用）
        'app' => [
            'appid'  => env('WX_APP_APPID'),
            'secret' => env('WX_APP_SECRET'),
        ],
    ],

    'apple' => [
        // Apple 开发者账号的 Bundle ID / Service ID（Sign in with Apple）
        'client_id' => env('APPLE_CLIENT_ID'),
    ],

    'yidun' => [
        // 网易易盾一键登录（本机号码校验）
        'secret_id'   => env('YIDUN_SECRET_ID'),
        'business_id' => env('YIDUN_BUSINESS_ID'),
        'secret_key'  => env('YIDUN_SECRET_KEY'),
    ],

    // ============ 对象存储（none 本地 / cos 腾讯云 / aliyun 阿里云）============
    'storage' => [
        'default' => env('CLOUD_DRIVER', 'none'),

        // 腾讯云 COS（default=cos 时填写，公有读 + 自定义域名，不签名）
        'cos' => [
            'secret_id'  => env('COS_SECRET_ID'),
            'secret_key' => env('COS_SECRET_KEY'),
            'region'     => env('COS_REGION', 'ap-beijing'),
            'bucket'     => env('COS_BUCKET'),
            'domain'     => env('COS_DOMAIN'),
            'prefix'     => env('COS_PREFIX', ''),
        ],

        // 阿里云 OSS（default=aliyun 时填写，公有读 + 自定义域名，不签名）
        'aliyun' => [
            'access_key_id'     => env('ALIYUN_OSS_ACCESS_KEY_ID'),
            'access_key_secret' => env('ALIYUN_OSS_ACCESS_KEY_SECRET'),
            'endpoint'          => env('ALIYUN_OSS_ENDPOINT'),
            'bucket'            => env('ALIYUN_OSS_BUCKET'),
            'domain'            => env('ALIYUN_OSS_DOMAIN'),
            'prefix'            => env('ALIYUN_OSS_PREFIX', ''),
        ],
    ],

];
