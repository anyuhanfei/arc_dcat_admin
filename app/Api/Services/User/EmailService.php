<?php

namespace App\Api\Services\User;

use App\Api\Repositories\User\UserRepository;
use App\Integrations\Email\Email;
use App\Models\User;

class EmailService
{
    public function __construct(
        protected VerifyTokenService $verifyToken,
        protected UserRepository $repository,
    ) {}

    /**
     * 一步换绑邮箱（旧邮箱 + 新邮箱验证码一起验证）
     */
    public function update(User $user, string $oldEmail, string $oldCode, string $newEmail, string $newCode): User
    {
        if (!$user->email) {
            throwBusinessException('请先绑定邮箱');
        }

        if ($oldEmail !== $user->email) {
            throwBusinessException('旧邮箱与当前账号不符');
        }

        if (!(new Email)->verifyCode($oldEmail, $oldCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        (new Email)->destroyCode($oldEmail);

        if (!(new Email)->verifyCode($newEmail, $newCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        (new Email)->destroyCode($newEmail);

        $this->checkNewEmail($user, $newEmail);

        $user->email = $newEmail;
        $user->email_verified_at = now();
        $this->repository->save($user);

        return $user;
    }

    /**
     * 两步换绑第一步：验证旧邮箱，签发 verify_token
     */
    public function verifyOld(User $user, string $oldEmail, string $oldCode): string
    {
        if (!$user->email) {
            throwBusinessException('请先绑定邮箱');
        }

        if ($oldEmail !== $user->email) {
            throwBusinessException('旧邮箱与当前账号不符');
        }

        if (!(new Email)->verifyCode($oldEmail, $oldCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        (new Email)->destroyCode($oldEmail);

        return $this->verifyToken->issue('change_email_old', $user);
    }

    /**
     * 两步换绑第二步：校验 verify_token + 新邮箱验证码，换绑
     */
    public function change(User $user, string $verifyToken, string $newEmail, string $newCode): User
    {
        if (!$this->verifyToken->verify($verifyToken, 'change_email_old', $user)) {
            throwBusinessException('验证已过期，请重新验证');
        }

        if (!(new Email)->verifyCode($newEmail, $newCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        (new Email)->destroyCode($newEmail);

        $this->checkNewEmail($user, $newEmail);

        $user->email = $newEmail;
        $user->email_verified_at = now();
        $this->repository->save($user);

        return $user;
    }

    /**
     * 只验证新邮箱一步换绑
     */
    public function updateNew(User $user, string $newEmail, string $newCode): User
    {
        if (!(new Email)->verifyCode($newEmail, $newCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        (new Email)->destroyCode($newEmail);

        $this->checkNewEmail($user, $newEmail);

        $user->email = $newEmail;
        $user->email_verified_at = now();
        $this->repository->save($user);

        return $user;
    }

    /**
     * 新邮箱唯一性检查（已被其他账号占用则抛异常）
     */
    protected function checkNewEmail(User $user, string $newEmail): void
    {
        $target = $this->repository->findByEmail($newEmail);

        if ($target !== null && (int) $target->id !== (int) $user->id) {
            throwBusinessException('该邮箱已绑定其他账号');
        }
    }
}
