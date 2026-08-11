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

];
