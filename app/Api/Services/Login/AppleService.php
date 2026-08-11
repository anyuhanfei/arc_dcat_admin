<?php

namespace App\Api\Services\Login;

use App\Integrations\OAuth\AppleLogin;
use App\Models\User;

class AppleService
{
    public function __construct(
        protected UserOauthAccountService $account,
    ) {}

    /**
     * Apple 登录（identityToken 验签，未绑定自动注册）
     */
    public function login(string $identityToken): array
    {
        $data = AppleLogin::make()->oauth($identityToken)['data'];

        return $this->account->loginByOauth(
            'apple',
            $data['openid'],
            null,
            null,
            null,
            null,
            [
                'email'            => $data['email'] ?? null,
                'is_private_email' => $data['is_private_email'] ?? false,
            ],
            'apple_' . $data['openid'],
            '苹果用户' . mb_substr($data['openid'], -6),
        );
    }

    /**
     * 绑定手机号（含账号合并）
     */
    public function bindPhone(User $user, string $phone, string $code): array
    {
        return $this->account->bindPhone($user, $phone, $code);
    }
}
