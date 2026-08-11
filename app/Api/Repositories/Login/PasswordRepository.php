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
     * 创建用户（id 由 User::generateId() 生成；并发撞 id 时自动重试）
     */
    public function createUser(array $data): User
    {
        $hasId = isset($data['id']);

        for ($attempt = 0; $attempt < 3; $attempt++) {
            if (! $hasId) {
                $data['id'] = User::generateId();
            }

            try {
                return User::create($data);
            } catch (\Illuminate\Database\QueryException $e) {
                // 主键/唯一冲突（MySQL 1062）：随机 id 并发撞车，重试；其他错误直接抛出
                if (! $hasId && (($e->errorInfo[1] ?? null) === 1062 || str_contains($e->getMessage(), 'Duplicate entry'))) {
                    continue;
                }
                throw $e;
            }
        }

        throwBusinessException('注册失败，请稍后重试');
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
