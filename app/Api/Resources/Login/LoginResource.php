<?php

namespace App\Api\Resources\Login;

use App\Api\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * 登录/绑定结果输出转换（token + 用户信息）
     */
    public function toArray($request): array
    {
        return [
            'token' => $this->token,
            'user'  => (new UserResource($this->user))->toArray($request),
        ];
    }
}
