<?php

namespace App\Api\Resources\Sys;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCategoriesResource extends JsonResource
{
    /**
     * 文章分类输出转换
     */
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'image' => full_url($this->image),
        ];
    }
}
