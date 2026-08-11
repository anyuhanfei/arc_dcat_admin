<?php

namespace App\Integrations\OAuth;

use Illuminate\Support\Facades\Http;

/**
 * 网易易盾一键登录（本机号码校验）- 三方封装
 *
 * token/accessToken 由易盾客户端 SDK 获取（一键登录拉起授权后回调）；
 * 校验通过返回手机号，业务层直接按手机号登录。
 *
 * 签名算法：ksort($params) 后按 key+value 无分隔符拼接，末尾追加 secretKey，md5（签名前参数值统一 utf8）。
 */
class YidunMobile
{
    private const URL = 'https://ye.dun.163.com/v1/oneclick/check';

    public function __construct(
        protected string $secretId,
        protected string $businessId,
        protected string $secretKey,
    ) {}

    public static function make(): static
    {
        $config = config('services.yidun');

        if (empty($config['secret_id']) || empty($config['business_id']) || empty($config['secret_key'])) {
            throwBusinessException('易盾配置错误，请检查 .env 中的 YIDUN_* 配置');
        }

        return new static(
            $config['secret_id'],
            $config['business_id'],
            $config['secret_key'],
        );
    }

    /**
     * 本机号码校验：换取手机号
     *
     * @param string $token 易盾客户端 SDK 返回的 token
     * @param string $accessToken 易盾客户端 SDK 返回的 accessToken
     * @return array ['success' => bool, 'data' => ['phone' => string]]
     */
    public function oauth(string $token, string $accessToken): array
    {
        $params = [
            'token'       => $token,
            'accessToken' => $accessToken,
            'secretId'    => $this->secretId,
            'businessId'  => $this->businessId,
            'version'     => 'v1',
            'timestamp'   => sprintf('%d', round(microtime(true) * 1000)),
            'nonce'       => substr(md5(uniqid('', true)), 0, 32),
        ];

        $params = $this->toUtf8($params);
        $params['signature'] = $this->genSignature($params);

        $response = Http::asForm()->timeout(5)->post(self::URL, $params);
        $result = $response->json();

        if ($response->failed() || ! is_array($result)) {
            throwBusinessException('易盾校验失败');
        }

        if ($result['code'] == 401) {
            throwBusinessException('登录失败');
        }
        if ($result['code'] == 405) {
            throwBusinessException('异常');
        }
        if ($result['code'] != 0) {
            throwBusinessException('易盾校验失败');
        }

        return [
            'success' => true,
            'data' => [
                'phone' => $result['data']['phone'] ?? '',
            ],
        ];
    }

    /**
     * 易盾签名：ksort 后 key+value 无分隔符拼接 + secretKey，md5
     */
    protected function genSignature(array $params): string
    {
        ksort($params);
        $buff = '';
        foreach ($params as $key => $value) {
            $buff .= $key;
            $buff .= $value;
        }
        $buff .= $this->secretKey;

        return md5(mb_convert_encoding($buff, 'utf8', 'auto'));
    }

    /**
     * 参数值统一转换为 utf8 编码
     */
    protected function toUtf8(array $params): array
    {
        $utf8s = [];
        foreach ($params as $key => $value) {
            $utf8s[$key] = is_string($value) ? mb_convert_encoding($value, 'utf8', 'auto') : $value;
        }

        return $utf8s;
    }
}
