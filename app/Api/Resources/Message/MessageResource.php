<?php

namespace App\Api\Resources\Message;

use App\Enums\MessageType;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * 消息列表输出转换
     */
    public function toArray($request): array
    {
        $typeValue = $this->type instanceof MessageType ? $this->type->value : (int) $this->type;

        $createdAt = $this->created_at instanceof \Carbon\Carbon
            ? $this->created_at
            : \Carbon\Carbon::parse($this->created_at);

        return [
            'id'            => (int) $this->id,
            'type'          => $typeValue,
            'type_label'    => MessageType::from($typeValue)->label(),
            'title'         => (string) $this->title,
            'image'         => full_url($this->image),
            'content'       => mb_substr(strip_tags((string) $this->content), 0, 100),
            'is_read'       => (int) $this->is_read,
            'created_at'    => format_relative_time($createdAt),
            'relation_type' => $this->relation_type,
            'relation_id'   => $this->relation_id,
        ];
    }
}
