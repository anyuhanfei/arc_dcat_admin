<?php

namespace App\Api\Resources\Sys;

use Illuminate\Http\Resources\Json\JsonResource;

class FaqCategoriesResource extends JsonResource
{
    /**
     * FAQ 分类输出转换
     */
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
