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
            'token' => $this->resource['token'] ?? null,
            'user'  => (new UserResource($this->resource['user'] ?? null))->toArray($request),
        ];
    }
}
