<?php

namespace App\Integrations\Sms;

use Illuminate\Support\Facades\Http;

/**
 * 阿里云短信 - 短信三方封装
 *
 * 阿里云短信 RPC API：https://dysmsapi.aliyuncs.com（Action=SendSms）
 * 请求体为 form-urlencoded，含 RPC 签名（HMAC-SHA1）
 */
class AliyunSms
{
    private const ENDPOINT = 'https://dysmsapi.aliyuncs.com';

    public function __construct(
        protected string $accessKeyId,
        protected string $accessKeySecret,
        protected string $signName,
    ) {}

    public static function make(): static
    {
        $config = config('services.sms.aliyun');

        if (empty($config['access_key_id']) || empty($config['access_key_secret']) || empty($config['sign_name'])) {
            throwBusinessException('阿里云短信配置错误，请检查 .env 中的 ALIYUN_SMS_* 配置');
        }

        return new static(
            $config['access_key_id'],
            $config['access_key_secret'],
            $config['sign_name'],
        );
    }

    /**
     * 发送验证码短信
     *
     * @return array ['success' => bool, 'data' => ['phone' => string]]
     */
    public function sendCode(string $phone, string $code): array
    {
        return $this->send($phone, config('services.sms.aliyun.templates.code'), ['code' => $code]);
    }

    /**
     * 发送通知模板短信
     *
     * @param string $template 配置中的模板键名，如 code / notice
     * @param array $params 模板参数，会作为 TemplateParam JSON 传递
     * @return array ['success' => bool, 'data' => ['phone' => string]]
     */
    public function sendTemplate(string $phone, string $template, array $params = []): array
    {
        $templateCode = config("services.sms.aliyun.templates.$template");
        if (empty($templateCode)) {
            throwBusinessException('短信模板不存在');
        }

        return $this->send($phone, $templateCode, $params);
    }

    /**
     * 调用阿里云短信 API 发送
     */
    private function send(string $phone, string $templateCode, array $params): array
    {
        $parameters = [
            'AccessKeyId'      => $this->accessKeyId,
            'Action'           => 'SendSms',
            'Format'           => 'JSON',
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureNonce'   => md5(uniqid((string) mt_rand(), true)),
            'SignatureVersion' => '1.0',
            'Timestamp'        => gmdate('Y-m-d\TH:i:s\Z'),
            'Version'          => '2017-05-25',
            'PhoneNumbers'     => $phone,
            'SignName'         => $this->signName,
            'TemplateCode'     => $templateCode,
            'TemplateParam'    => json_encode($params, JSON_UNESCAPED_UNICODE),
        ];

        ksort($parameters);

        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $canonicalizedQueryString = substr($canonicalizedQueryString, 1);

        $stringToSign = 'POST&' . $this->percentEncode('/') . '&' . $this->percentEncode($canonicalizedQueryString);
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $this->accessKeySecret . '&', true));

        $parameters['Signature'] = $signature;

        $response = Http::timeout(5)->asForm()->post(self::ENDPOINT, $parameters);

        $result = $response->json();
        if (($result['Code'] ?? '') !== 'OK') {
            throwBusinessException($result['Message'] ?? '短信发送失败');
        }

        return ['success' => true, 'data' => ['phone' => $phone]];
    }

    /**
     * RFC3986 百分号编码（阿里云 RPC 规范）
     */
    private function percentEncode(string $value): string
    {
        return str_replace(['+', '*', '%7E'], ['%20', '%2A', '~'], rawurlencode($value));
    }
}
