<?php

namespace App\Api\Services\Ms;

use App\Integrations\Sms\Sms;
use App\Models\User;

class SmsService
{
    /**
     * 发送短信验证码
     *
     * @param string|null $phone 手机号（可选，不传则使用登录用户手机号）
     * @param User|null   $user  当前登录用户（未登录为 null）
     */
    public function send(?string $phone, ?User $user): array
    {
        if (!$phone) {
            if (!$user) {
                throwBusinessException('请先登录或输入手机号');
            }

            if (!$user->phone) {
                throwBusinessException('当前账号未绑定手机号');
            }

            $phone = $user->phone;
        }

        Sms::make()->sendCode($phone);

        return ['phone' => $phone];
    }
}
