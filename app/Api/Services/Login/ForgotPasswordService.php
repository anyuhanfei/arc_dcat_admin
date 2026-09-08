<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\PasswordRepository;
use App\Integrations\Email\Email;
use App\Integrations\Sms\Sms;

class ForgotPasswordService
{
    public function __construct(
        protected PasswordRepository $repository,
    ) {}

    /**
     * 手机号验证码重置密码（重置后清空该用户所有已登录 token）
     */
    public function phone(string $phone, string $code, string $newPassword): void
    {
        $user = $this->repository->findByPhone($phone);

        if (!$user) {
            throwBusinessException('该手机号未注册');
        }

        if (!Sms::make()->verifyCode($phone, $code)) {
            throwBusinessException('验证码错误或已过期');
        }
        Sms::make()->destroyCode($phone);

        $user->password = $newPassword;
        $this->repository->save($user);

        // 安全：重置密码后让该用户已登录的 token 全部失效
        $user->tokens()->delete();
    }

    /**
     * 邮箱验证码重置密码（重置后清空该用户所有已登录 token）
     */
    public function email(string $email, string $code, string $newPassword): void
    {
        $user = $this->repository->findByEmail($email);

        if (!$user) {
            throwBusinessException('该邮箱未注册');
        }

        if (!(new Email)->verifyCode($email, $code)) {
            throwBusinessException('验证码错误或已过期');
        }
        (new Email)->destroyCode($email);

        $user->password = $newPassword;
        $this->repository->save($user);

        // 安全：重置密码后让该用户已登录的 token 全部失效
        $user->tokens()->delete();
    }
}
