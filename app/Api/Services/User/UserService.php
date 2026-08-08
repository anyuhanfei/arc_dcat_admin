<?php

namespace App\Api\Services\User;

use App\Models\User;

class UserService
{
    /**
     * 用户公开信息（与登录接口返回的 user 格式一致）
     */
    public function info(User $user): array
    {
        return [
            'id'         => $user->id,
            'account'    => $user->account,
            'phone'      => $user->phone,
            'email'      => $user->email,
            'nickname'   => $user->nickname,
            'avatar'     => full_url($user->avatar),
            'signature'  => $user->signature,
            'gender'     => $user->gender,
            'birthday'   => $user->birthday?->toDateString(),
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}
