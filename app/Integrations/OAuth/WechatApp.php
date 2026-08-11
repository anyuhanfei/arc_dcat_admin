<?php

namespace App\Integrations\OAuth;

use Illuminate\Support\Facades\Http;

/**
 * 微信 APP（开放平台移动应用）- 登录授权三方封装
 *
 * 1. oauth：code 换 access_token/openid/unionid
 *    GET https://api.weixin.qq.com/sns/oauth2/access_token?appid=&secret=&code=&grant_type=authorization_code
 * 2. 用户信息（昵称/头像）：GET sns/userinfo，失败静默不影响登录
 */
class WechatApp
{
    public function __construct(
        protected string $appid,
        protected string $secret,
    ) {}

    public static function make(): static
    {
        $config = config('services.wx.app');

        if (empty($config['appid']) || empty($config['secret'])) {
            throwBusinessException('微信APP配置错误，请检查 .env 中的 WX_APP_* 配置');
        }

        return new static(
            $config['appid'],
            $config['secret'],
        );
    }

    /**
     * code 换 openid/unionid + 用户信息（userinfo 失败静默）
     *
     * @return array ['success' => bool, 'data' => ['openid' => string, 'unionid' => ?string, 'nickname' => ?string, 'avatar' => ?string]]
     */
    public function oauth(string $code): array
    {
        $response = Http::timeout(5)->get('https://api.weixin.qq.com/sns/oauth2/access_token', [
            'appid'      => $this->appid,
            'secret'     => $this->secret,
            'code'       => $code,
            'grant_type' => 'authorization_code',
        ]);

        $data = $response->json() ?? [];
        if ($response->failed() || (isset($data['errcode']) && (int) $data['errcode'] !== 0)) {
            throwBusinessException('微信登录失败' . ($data['errmsg'] ?? ''));
        }

        $openid = $data['openid'] ?? '';
        $unionid = $data['unionid'] ?? null;
        $nickname = null;
        $avatar = null;

        // 用户信息接口失败静默（不影响登录，昵称/头像留空）
        $userinfo = Http::timeout(5)->get('https://api.weixin.qq.com/sns/userinfo', [
            'access_token' => $data['access_token'] ?? '',
            'openid'       => $openid,
            'lang'         => 'zh_CN',
        ])->json();

        if (is_array($userinfo) && ! isset($userinfo['errcode'])) {
            $nickname = $userinfo['nickname'] ?? null;
            $avatar = $userinfo['headimgurl'] ?? null;
            $unionid = $unionid ?? ($userinfo['unionid'] ?? null);
        }

        return [
            'success' => true,
            'data' => [
                'openid'   => $openid,
                'unionid'  => $unionid,
                'nickname' => $nickname,
                'avatar'   => $avatar,
            ],
        ];
    }
}
