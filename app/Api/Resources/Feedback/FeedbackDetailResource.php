<?php

namespace App\Api\Resources\Feedback;

use App\Enums\FeedbackStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackDetailResource extends JsonResource
{
    /**
     * 意见反馈详情输出转换（不含 admin_remark 对内备注）
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'title'         => $this->title,
            'content'       => $this->content,
            'images'        => is_array($this->images)
                ? array_map(fn (string $url): string => full_url($url), $this->images)
                : [],
            'contact_name'  => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'status'        => $this->status instanceof FeedbackStatus ? $this->status->value : (int) $this->status,
            'status_name'   => $this->status instanceof FeedbackStatus ? $this->status->label() : (string) $this->status,
            'handler_id'    => $this->handler_id,
            'handled_at'    => $this->handled_at?->format('Y-m-d H:i:s'),
            'reply'         => $this->reply,
            'replied_at'    => $this->replied_at?->format('Y-m-d H:i:s'),
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
