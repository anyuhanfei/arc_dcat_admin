<?php

namespace App\Api\Services\Login;

use App\Integrations\OAuth\WechatMp;
use App\Models\User;

class WechatMpService
{
    public function __construct(
        protected WechatAccountService $account,
    ) {}

    /**
     * 公众号登录（网页授权 code，未绑定自动注册）
     */
    public function login(string $code): array
    {
        $data = WechatMp::make()->oauth($code)['data'];

        return $this->account->loginByOpenid('wechat_mp', $data['openid'], $data['unionid'] ?? null, null, $data['nickname'] ?? null, $data['avatar'] ?? null);
    }

    /**
     * 绑定手机号（含账号合并）
     */
    public function bindPhone(User $user, string $phone, string $code): array
    {
        return $this->account->bindPhone($user, $phone, $code);
    }
}
