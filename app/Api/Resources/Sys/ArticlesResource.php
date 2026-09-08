<?php

namespace App\Api\Resources\Sys;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticlesResource extends JsonResource
{
    /**
     * 文章列表/详情输出转换
     * 列表时 summary/keywords/content 字段自动隐藏
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'category_id'   => $this->category_id,
            'category_name' => $this->category->name ?? '',
            'title'         => $this->title,
            'image'         => full_url($this->image),
            'author'        => $this->author,
            'published_at'  => $this->created_at->format('Y-m-d H:i:s'),
            'summary'       => $this->when($this->summary !== null, $this->summary),
            'keywords'      => $this->when($this->keywords !== null, $this->keywords ? array_values(array_filter(array_map('trim', explode(',', $this->keywords)))) : []),
            'content'       => $this->when($this->content !== null, $this->content),
        ];
    }
}
