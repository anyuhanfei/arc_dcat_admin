<?php

namespace App\Api\Services\Login;

use App\Integrations\OAuth\WechatMini;
use App\Models\User;

class WechatMiniService
{
    public function __construct(
        protected WechatAccountService $account,
    ) {}

    /**
     * 小程序登录（code 换 openid，未绑定自动注册；session_key 持久化供手机号解密）
     */
    public function login(string $code): array
    {
        $data = WechatMini::make()->code2Session($code)['data'];

        return $this->account->loginByOpenid('wechat_mini', $data['openid'], $data['unionid'] ?? null, $data['session_key'] ?? null);
    }

    /**
     * 绑定手机号（含账号合并）
     */
    public function bindPhone(User $user, string $phone, string $code): array
    {
        return $this->account->bindPhone($user, $phone, $code);
    }
}
