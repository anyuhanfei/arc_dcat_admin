<?php

namespace App\Api\Resources\Feedback;

use App\Enums\FeedbackStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /**
     * 意见反馈列表输出转换
     */
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'status'      => $this->status instanceof FeedbackStatus ? $this->status->value : (int) $this->status,
            'status_name' => $this->status instanceof FeedbackStatus ? $this->status->label() : (string) $this->status,
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'handled_at'  => $this->handled_at?->format('Y-m-d H:i:s'),
        ];
    }
}
