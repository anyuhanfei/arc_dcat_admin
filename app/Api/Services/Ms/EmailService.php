<?php

namespace App\Api\Services\Ms;

use App\Integrations\Email\Email;
use App\Models\User;

class EmailService
{
    public function __construct(
        protected Email $email,
    ) {}

    /**
     * 发送邮箱验证码
     *
     * @param string|null $email 邮箱（可选，不传则使用登录用户邮箱）
     * @param User|null   $user  当前登录用户（未登录为 null）
     */
    public function send(?string $email, ?User $user): array
    {
        if (!$email) {
            if (!$user) {
                throwBusinessException('请先登录或输入邮箱');
            }

            if (!$user->email) {
                throwBusinessException('当前账号未绑定邮箱');
            }

            $email = $user->email;
        }

        $this->email->sendCode($email);

        return ['email' => $email];
    }
}
