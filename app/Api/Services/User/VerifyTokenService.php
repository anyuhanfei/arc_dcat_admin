<?php

namespace App\Api\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * 两步验证标识（verify_token）服务
 * 第一步验证成功 → issue() 签发标识；第二步 verify() 校验（一次性，TTL 600 秒）
 */
class VerifyTokenService
{
    private const TTL = 600;
    private const PREFIX = 'verify_token:';

    /**
     * 签发验证标识
     *
     * @param string $scene 场景标识（如 change_password_phone / change_password_old / change_phone_old）
     * @return string 随机验证标识（前端保存，第二步提交）
     */
    public function issue(string $scene, User $user): string
    {
        $token = Str::random(40);
        Cache::put(self::PREFIX . $token, ['scene' => $scene, 'user_id' => $user->id], self::TTL);
        return $token;
    }

    /**
     * 校验验证标识（校验成功即销毁，一次性）
     */
    public function verify(string $token, string $scene, User $user): bool
    {
        $data = Cache::get(self::PREFIX . $token);
        if ($data === null || $data['scene'] !== $scene || (int) $data['user_id'] !== (int) $user->id) {
            return false;
        }
        Cache::forget(self::PREFIX . $token);
        return true;
    }
}
