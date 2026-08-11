<?php

namespace App\Integrations\OAuth;

use Illuminate\Support\Facades\Http;

/**
 * 微信小程序 - 登录授权三方封装
 *
 * 1. code2Session：wx.login 的 code 换 openid/session_key/unionid
 *    GET https://api.weixin.qq.com/sns/jscode2session?appid=&secret=&js_code=&grant_type=authorization_code
 * 2. getPhone：手机号解密（旧方式：wx.login 后前端传 encryptedData+iv，用 session_key 做 AES-128-CBC 解密）
 *    必须校验 watermark.appid === 本小程序 appid
 */
class WechatMini
{
    public function __construct(
        protected string $appid,
        protected string $secret,
    ) {}

    public static function make(): static
    {
        $config = config('services.wx.mini');

        if (empty($config['appid']) || empty($config['secret'])) {
            throwBusinessException('微信小程序配置错误，请检查 .env 中的 WX_MINI_* 配置');
        }

        return new static(
            $config['appid'],
            $config['secret'],
        );
    }

    /**
     * code 换 openid / session_key / unionid
     *
     * @return array ['success' => bool, 'data' => ['openid' => string, 'session_key' => string, 'unionid' => ?string]]
     */
    public function code2Session(string $code): array
    {
        $response = Http::timeout(5)->get('https://api.weixin.qq.com/sns/jscode2session', [
            'appid'      => $this->appid,
            'secret'     => $this->secret,
            'js_code'    => $code,
            'grant_type' => 'authorization_code',
        ]);

        $data = $response->json() ?? [];
        if ($response->failed() || (isset($data['errcode']) && (int) $data['errcode'] !== 0)) {
            throwBusinessException('微信登录失败' . ($data['errmsg'] ?? ''));
        }

        return [
            'success' => true,
            'data' => [
                'openid'      => $data['openid'] ?? '',
                'session_key' => $data['session_key'] ?? '',
                'unionid'     => $data['unionid'] ?? null,
            ],
        ];
    }

    /**
     * 手机号解密（AES-128-CBC，session_key 为 base64 字符串，解密 key 取其 base64 解码后的 16 字节）
     *
     * @return array ['success' => bool, 'data' => ['phoneNumber' => ?string, 'purePhoneNumber' => ?string]]
     */
    public function getPhone(string $sessionKey, string $iv, string $encryptedData): array
    {
        $decrypted = openssl_decrypt(
            base64_decode($encryptedData),
            'AES-128-CBC',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv),
        );

        $data = $decrypted === false ? null : json_decode($decrypted, true);
        if (! is_array($data) || ($data['watermark']['appid'] ?? null) !== $this->appid) {
            throwBusinessException('手机号解密失败');
        }

        return [
            'success' => true,
            'data' => [
                'phoneNumber'      => $data['phoneNumber'] ?? null,
                'purePhoneNumber'  => $data['purePhoneNumber'] ?? null,
            ],
        ];
    }
}
