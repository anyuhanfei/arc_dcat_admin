<?php

namespace App\Api\Services\Login;

use App\Integrations\OAuth\WechatApp;
use App\Models\User;

class WechatAppService
{
    public function __construct(
        protected WechatAccountService $account,
    ) {}

    /**
     * APP 登录（code 换 openid，未绑定自动注册）
     */
    public function login(string $code): array
    {
        $data = WechatApp::make()->oauth($code)['data'];

        return $this->account->loginByOpenid('wechat_app', $data['openid'], $data['unionid'] ?? null, null, $data['nickname'] ?? null, $data['avatar'] ?? null);
    }

    /**
     * 绑定手机号（含账号合并）
     */
    public function bindPhone(User $user, string $phone, string $code): array
    {
        return $this->account->bindPhone($user, $phone, $code);
    }
}
