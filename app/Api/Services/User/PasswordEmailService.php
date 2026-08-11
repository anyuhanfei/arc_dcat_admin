<?php

namespace App\Api\Services\User;

use App\Integrations\Email\Email;
use App\Models\User;

class PasswordEmailService
{
    public function __construct(
        protected VerifyTokenService $verifyToken,
    ) {}

    /**
     * 一步改密（邮箱验证码）
     */
    public function update(User $user, ?string $email, string $code, string $newPassword): void
    {
        $email = $email ?: $user->email;

        if (!$email) {
            throwBusinessException('请先绑定邮箱');
        }

        if (!(new Email)->verifyCode($email, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        (new Email)->destroyCode($email);

        $user->password = $newPassword;
        $user->save();
    }

    /**
     * 两步改密第一步：邮箱验证码验证通过后签发 verify_token
     */
    public function verify(User $user, ?string $email, string $code): string
    {
        $email = $email ?: $user->email;

        if (!$email) {
            throwBusinessException('请先绑定邮箱');
        }

        if (!(new Email)->verifyCode($email, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        (new Email)->destroyCode($email);

        return $this->verifyToken->issue('change_password_email', $user);
    }

    /**
     * 两步改密第二步：校验 verify_token 后修改密码
     */
    public function change(User $user, string $verifyToken, string $newPassword): void
    {
        if (!$this->verifyToken->verify($verifyToken, 'change_password_email', $user)) {
            throwBusinessException('验证已过期，请重新验证');
        }

        $user->password = $newPassword;
        $user->save();
    }
}
