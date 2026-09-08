<?php

namespace App\Api\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * 用户公开信息输出转换
     */
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'account'    => $this->account,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'nickname'   => $this->nickname,
            'avatar'     => full_url($this->avatar),
            'signature'  => $this->signature,
            'gender'     => match ((int) $this->gender) {
                1 => '男',
                2 => '女',
                default => '未知',
            },
            'age'        => $this->birthday?->diffInYears(now()),
            'birthday'   => $this->birthday?->toDateString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
