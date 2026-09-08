<?php

namespace App\Api\Repositories\User;

use App\Models\User;

class UserRepository
{
    /**
     * 根据 ID 获取用户
     */
    public function findById(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * 根据邮箱查找用户（不存在返回 null）
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * 根据手机号查找用户（不存在返回 null）
     */
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    /**
     * 保存用户（新增或更新）
     */
    public function save(User $user): void
    {
        $user->save();
    }

    /**
     * 删除用户（软删除）
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
