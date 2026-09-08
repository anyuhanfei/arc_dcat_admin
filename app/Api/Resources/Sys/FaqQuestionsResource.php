<?php

namespace App\Api\Resources\Sys;

use Illuminate\Http\Resources\Json\JsonResource;

class FaqQuestionsResource extends JsonResource
{
    /**
     * FAQ 问题输出转换
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'category_id'   => $this->category_id,
            'category_name' => $this->category->name ?? '',
            'question'      => $this->question,
            'answer'        => $this->answer,
        ];
    }
}
