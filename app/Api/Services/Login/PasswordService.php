<?php

namespace App\Api\Services\Login;

use App\Api\Repositories\Login\PasswordRepository;
use App\Integrations\Sms\Sms;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordService
{
    public function __construct(
        protected PasswordRepository $repository,
    ) {}

    /**
     * 注册
     */
    public function register(string $account, string $password): array
    {
        $nickname = '用户' . mb_substr($account, -4);

        $user = $this->repository->createUser([
            'account'  => $account,
            'password' => $password,
            'nickname' => $nickname,
        ]);

        return $this->userInfo($user);
    }

    /**
     * 登录
     */
    public function login(string $account, string $password): array
    {
        $user = $this->repository->findByLogin($account);

        if (!$user || !Hash::check($password, $user->password)) {
            throwBusinessException('账号或密码错误');
        }

        if ((int) $user->status !== 1) {
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
     * 手机号注册（账号密码 + 手机验证码）
     */
    public function registerByPhone(string $account, string $password, string $phone, string $code): array
    {
        if ($this->repository->findByPhone($phone)) {
            throwBusinessException('该手机号已注册');
        }

        if ($this->repository->findByAccount($account)) {
            throwBusinessException('账号已存在');
        }

        if (!Sms::make()->verifyCode($phone, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        $user = $this->repository->createUser([
            'account'  => $account,
            'password' => $password,
            'phone'    => $phone,
            'nickname' => '用户' . mb_substr($account, -4),
        ]);

        // 注册成功，销毁验证码缓存防止重放
        Sms::make()->destroyCode($phone);

        return $this->userInfo($user);
    }

    /**
     * 邮箱注册（账号密码 + 邮箱验证码）
     */
    public function registerByEmail(string $account, string $password, string $email, string $code): array
    {
        if ($this->repository->findByEmail($email)) {
            throwBusinessException('该邮箱已注册');
        }

        if ($this->repository->findByAccount($account)) {
            throwBusinessException('账号已存在');
        }

        if (!(new \App\Integrations\Email\Email)->verifyCode($email, $code)) {
            throwBusinessException('验证码错误或已过期');
        }

        $user = $this->repository->createUser([
            'account'  => $account,
            'password' => $password,
            'email'    => $email,
            'nickname' => '用户' . mb_substr($account, -4),
        ]);

        // 注册成功，销毁验证码缓存防止重放
        (new \App\Integrations\Email\Email)->destroyCode($email);

        return $this->userInfo($user);
    }

    /**
     * 用户公开信息（统一委托 User::userInfo()）
     */
    protected function userInfo(User $user): array
    {
        return $user->userInfo();
    }
}
