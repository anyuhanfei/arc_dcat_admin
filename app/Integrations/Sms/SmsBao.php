<?php

namespace App\Integrations\Sms;

use Illuminate\Support\Facades\Http;

/**
 * 短信宝 - 短信三方封装
 *
 * 短信宝 HTTP GET API：http://api.smsbao.com/sms
 * 参数：u=用户名、p=md5(密码)、m=手机号、c=urlencode(签名+内容)
 */
class SmsBao
{
    /** 短信宝状态码 → 文案映射 */
    private const STATUS_TEXT = [
        '0'  => '短信发送成功',
        '-1' => '参数不全',
        '-2' => '服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！',
        '30' => '密码错误',
        '40' => '账号不存在',
        '41' => '余额不足',
        '42' => '帐户已过期',
        '43' => 'IP地址限制',
        '50' => '内容含有敏感词',
    ];

    private const API_URL = 'http://api.smsbao.com/sms';

    public function __construct(
        protected string $username,
        protected string $password,
        protected string $sign,
    ) {}

    public static function make(): static
    {
        $config = config('services.sms.smsbao');

        if (empty($config['username']) || empty($config['password']) || empty($config['sign'])) {
            throwBusinessException('短信宝配置错误，请检查 .env 中的 SMSBAO_* 配置');
        }

        return new static(
            $config['username'],
            $config['password'],
            $config['sign'],
        );
    }

    /**
     * 发送验证码短信
     *
     * @return array ['success' => bool, 'data' => ['phone' => string]]
     */
    public function sendCode(string $phone, string $code): array
    {
        $content = config('services.sms.smsbao.templates.code');

        return $this->send($phone, $this->replacePlaceholders($content, ['code' => $code]));
    }

    /**
     * 发送通知模板短信
     *
     * @param string $template 配置中的模板键名，如 code / notice
     * @param array $params 占位符参数，如 ['name' => '张三']，内容中 {name} 会被替换
     * @return array ['success' => bool, 'data' => ['phone' => string]]
     */
    public function sendTemplate(string $phone, string $template, array $params = []): array
    {
        $content = config("services.sms.smsbao.templates.$template");
        if (empty($content)) {
            throwBusinessException('短信模板不存在');
        }

        return $this->send($phone, $this->replacePlaceholders($content, $params));
    }

    /**
     * 替换内容中的 {key} 占位符
     */
    private function replacePlaceholders(string $content, array $params): string
    {
        foreach ($params as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }

    /**
     * 调用短信宝 API 发送
     */
    private function send(string $phone, string $content): array
    {
        $response = Http::timeout(5)->get(self::API_URL, [
            'u' => $this->username,
            'p' => md5($this->password),
            'm' => $phone,
            // Http::get 的 query 数组会经 http_build_query 自动 urlencode，此处传原始内容即可
            'c' => '【' . $this->sign . '】' . $content,
        ]);

        $status = trim($response->body());
        if ($status !== '0') {
            throwBusinessException(self::STATUS_TEXT[$status] ?? '短信发送失败');
        }

        return ['success' => true, 'data' => ['phone' => $phone]];
    }
}
