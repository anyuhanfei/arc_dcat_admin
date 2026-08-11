<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\PasswordRepository;
use App\Integrations\OAuth\YidunMobile;
use App\Models\User;

class YidunService
{
    public function __construct(
        protected PasswordRepository $repository,
    ) {}

    /**
     * 易盾一键登录（本机号码校验，未注册自动注册）
     */
    public function login(string $token, string $accessToken): array
    {
        $data = YidunMobile::make()->oauth($token, $accessToken)['data'];
        $phone = $data['phone'];

        $user = $this->repository->findByPhone($phone);

        if (!$user) {
            $user = $this->repository->createUser([
                'account'  => $phone,
                'phone'    => $phone,
                'nickname' => '用户' . mb_substr($phone, -4),
            ]);
        } elseif ((int) $user->status !== 1) {
            throwBusinessException('账号已被禁用');
        }

        $plainTextToken = $user->createToken('api')->plainTextToken;
        $token = explode('|', $plainTextToken, 2)[1] ?? $plainTextToken;

        return [
            'token' => $token,
            'user'  => $this->userInfo($user),
        ];
    }

    /**
     * 用户公开信息（统一委托 User::userInfo()）
     */
    protected function userInfo(User $user): array
    {
        return $user->userInfo();
    }
}
