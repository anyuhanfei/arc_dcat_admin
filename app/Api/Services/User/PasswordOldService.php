<?php

namespace App\Api\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordOldService
{
    public function __construct(
        protected VerifyTokenService $verifyToken,
    ) {}

    /**
     * 一步改密（旧密码校验）
     */
    public function update(User $user, string $oldPassword, string $newPassword): void
    {
        if (!Hash::check($oldPassword, $user->password)) {
            throwBusinessException('旧密码错误');
        }

        $user->password = $newPassword;
        $user->save();
    }

    /**
     * 两步改密第一步：旧密码校验通过后签发 verify_token
     */
    public function verify(User $user, string $oldPassword): string
    {
        if (!Hash::check($oldPassword, $user->password)) {
            throwBusinessException('旧密码错误');
        }

        return $this->verifyToken->issue('change_password_old', $user);
    }

    /**
     * 两步改密第二步：校验 verify_token 后修改密码
     */
    public function change(User $user, string $verifyToken, string $newPassword): void
    {
        if (!$this->verifyToken->verify($verifyToken, 'change_password_old', $user)) {
            throwBusinessException('验证已过期，请重新验证');
        }

        $user->password = $newPassword;
        $user->save();
    }
}
