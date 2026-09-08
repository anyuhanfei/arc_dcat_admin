<?php

namespace App\Api\Resources\Ms;

use Illuminate\Http\Resources\Json\JsonResource;

class SmsResource extends JsonResource
{
    /**
     * 短信发送结果输出转换
     */
    public function toArray($request): array
    {
        return [
            'phone' => $this->phone,
        ];
    }
}
