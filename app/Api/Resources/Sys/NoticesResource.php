<?php

namespace App\Api\Resources\Sys;

use Illuminate\Http\Resources\Json\JsonResource;

class NoticesResource extends JsonResource
{
    /**
     * 公告列表/详情输出转换
     * 列表时 content 字段自动隐藏
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'image'        => full_url($this->image),
            'published_at' => $this->created_at->format('Y-m-d H:i:s'),
            'content'      => $this->when($this->content !== null, $this->content),
        ];
    }
}
