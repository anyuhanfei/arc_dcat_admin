<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\PasswordRepository;
use App\Integrations\Email\Email;
use App\Models\User;

class EmailService
{
    public function __construct(
        protected PasswordRepository $repository,
    ) {}

    /**
     * 邮箱验证码登录（未注册自动注册）
     */
    public function login(string $email, string $code): array
    {
        if (!(new Email)->verifyCode($email, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        $user = $this->repository->findByEmail($email);

        if (!$user) {
            $user = $this->repository->createUser([
                'account'  => $email,
                'email'    => $email,
                'nickname' => '用户' . mb_substr($email, -4),
            ]);
        } elseif ((int) $user->status !== 1) {
            throwBusinessException('账号已被禁用');
        }

        $plainTextToken = $user->createToken('api')->plainTextToken;
        $token = explode('|', $plainTextToken, 2)[1] ?? $plainTextToken;

        // 登录成功，销毁验证码缓存防止重放
        (new Email)->destroyCode($email);

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
