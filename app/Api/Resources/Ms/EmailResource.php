<?php

namespace App\Api\Resources\Ms;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailResource extends JsonResource
{
    /**
     * 邮件发送结果输出转换
     */
    public function toArray($request): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
