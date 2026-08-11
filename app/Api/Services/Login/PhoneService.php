<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\PasswordRepository;
use App\Integrations\Sms\Sms;
use App\Models\User;

class PhoneService
{
    public function __construct(
        protected PasswordRepository $repository,
    ) {}

    /**
     * 手机号验证码登录（未注册自动注册）
     */
    public function login(string $phone, string $code): array
    {
        if (!Sms::make()->verifyCode($phone, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

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

        // 登录成功，销毁验证码缓存防止重放
        Sms::make()->destroyCode($phone);

        return [
            'token' => $token,
            'user'  => $this->userInfo($user),
        ];
    }

    /**
     * 用户公开信息
     */
    protected function userInfo(User $user): array
    {
        return [
            'id'         => $user->id,
            'account'    => $user->account,
            'phone'      => $user->phone,
            'email'      => $user->email,
            'nickname'   => $user->nickname,
            'avatar'     => full_url($user->avatar),
            'signature'  => $user->signature,
            'gender'     => $user->gender,
            'birthday'   => $user->birthday?->toDateString(),
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}
