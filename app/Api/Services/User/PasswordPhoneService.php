<?php

namespace App\Api\Services\User;

use App\Integrations\Sms\Sms;
use App\Models\User;

class PasswordPhoneService
{
    public function __construct(
        protected VerifyTokenService $verifyToken,
    ) {}

    /**
     * 一步改密（手机号验证码）
     */
    public function update(User $user, ?string $phone, string $code, string $newPassword): void
    {
        $phone = $phone ?: $user->phone;

        if (!$phone) {
            throwBusinessException('请先绑定手机号');
        }

        if (!Sms::make()->verifyCode($phone, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        Sms::make()->destroyCode($phone);

        $user->password = $newPassword;
        $user->save();
    }

    /**
     * 两步改密第一步：手机号验证码验证通过后签发 verify_token
     */
    public function verify(User $user, ?string $phone, string $code): string
    {
        $phone = $phone ?: $user->phone;

        if (!$phone) {
            throwBusinessException('请先绑定手机号');
        }

        if (!Sms::make()->verifyCode($phone, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        Sms::make()->destroyCode($phone);

        return $this->verifyToken->issue('change_password_phone', $user);
    }

    /**
     * 两步改密第二步：校验 verify_token 后修改密码
     */
    public function change(User $user, string $verifyToken, string $newPassword): void
    {
        if (!$this->verifyToken->verify($verifyToken, 'change_password_phone', $user)) {
            throwBusinessException('验证已过期，请重新验证');
        }

        $user->password = $newPassword;
        $user->save();
    }
}
