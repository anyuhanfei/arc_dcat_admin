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
}
