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
}
