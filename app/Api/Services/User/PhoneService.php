<?php

namespace App\Api\Services\User;

use App\Api\Repositories\User\UserRepository;
use App\Integrations\Sms\Sms;
use App\Models\User;

class PhoneService
{
    public function __construct(
        protected VerifyTokenService $verifyToken,
        protected UserRepository $repository,
    ) {}

    /**
     * 一步换绑手机号（旧号 + 新号验证码一起验证）
     */
    public function update(User $user, string $oldPhone, string $oldCode, string $newPhone, string $newCode): User
    {
        if (!$user->phone) {
            throwBusinessException('请先绑定手机号');
        }

        if ($oldPhone !== $user->phone) {
            throwBusinessException('旧手机号与当前账号不符');
        }

        if (!Sms::make()->verifyCode($oldPhone, $oldCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        Sms::make()->destroyCode($oldPhone);

        if (!Sms::make()->verifyCode($newPhone, $newCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        Sms::make()->destroyCode($newPhone);

        $this->checkNewPhone($user, $newPhone);

        $user->phone = $newPhone;
        $user->phone_verified_at = now();
        $this->repository->save($user);

        return $user;
    }

    /**
     * 两步换绑第一步：验证旧手机号，签发 verify_token
     */
    public function verifyOld(User $user, string $oldPhone, string $oldCode): string
    {
        if (!$user->phone) {
            throwBusinessException('请先绑定手机号');
        }

        if ($oldPhone !== $user->phone) {
            throwBusinessException('旧手机号与当前账号不符');
        }

        if (!Sms::make()->verifyCode($oldPhone, $oldCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        Sms::make()->destroyCode($oldPhone);

        return $this->verifyToken->issue('change_phone_old', $user);
    }

    /**
     * 两步换绑第二步：校验 verify_token + 新手机号验证码，换绑
     */
    public function change(User $user, string $verifyToken, string $newPhone, string $newCode): User
    {
        if (!$this->verifyToken->verify($verifyToken, 'change_phone_old', $user)) {
            throwBusinessException('验证已过期，请重新验证');
        }

        if (!Sms::make()->verifyCode($newPhone, $newCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        Sms::make()->destroyCode($newPhone);

        $this->checkNewPhone($user, $newPhone);

        $user->phone = $newPhone;
        $user->phone_verified_at = now();
        $this->repository->save($user);

        return $user;
    }

    /**
     * 只验证新手机号一步换绑
     */
    public function updateNew(User $user, string $newPhone, string $newCode): User
    {
        if (!Sms::make()->verifyCode($newPhone, $newCode)) {
            throwBusinessException('验证码错误或已过期');
        }
        Sms::make()->destroyCode($newPhone);

        $this->checkNewPhone($user, $newPhone);

        $user->phone = $newPhone;
        $user->phone_verified_at = now();
        $this->repository->save($user);

        return $user;
    }

    /**
     * 新手机号唯一性检查（已被其他账号占用则抛异常）
     */
    protected function checkNewPhone(User $user, string $newPhone): void
    {
        $target = $this->repository->findByPhone($newPhone);

        if ($target !== null && (int) $target->id !== (int) $user->id) {
            throwBusinessException('该手机号已绑定其他账号');
        }
    }
}
