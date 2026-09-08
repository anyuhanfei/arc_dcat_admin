<?php

namespace App\Api\Resources\Sys;

use Illuminate\Http\Resources\Json\JsonResource;

class BannersResource extends JsonResource
{
    /**
     * 轮播图输出转换
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'position'        => $this->position,
            'position_name'   => config('business.banner.positions.'.$this->position.'.name.'.app()->getLocale(), $this->position),
            'image'           => full_url($this->image),
            'link_type'       => $this->link_type,
            'link_type_name'  => config('business.banner.link_types.'.$this->link_type.'.name.'.app()->getLocale(), $this->link_type),
            'link_value'      => $this->link_value,
        ];
    }
}
