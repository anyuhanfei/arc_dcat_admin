<?php

namespace App\Api\Repositories\Login;

use App\Models\User;

class PasswordRepository
{
    /**
     * 按 账号/手机号/邮箱 任一匹配查询用户
     */
    public function findByLogin(string $account): ?User
    {
        return User::where('account', $account)
            ->orWhere('phone', $account)
            ->orWhere('email', $account)
            ->first();
    }

    /**
     * 创建用户
     */
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * 按手机号查询用户
     */
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    /**
     * 按邮箱查询用户
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * 按账号查询用户
     */
    public function findByAccount(string $account): ?User
    {
        return User::where('account', $account)->first();
    }
}
