<?php

namespace App\Api\Repositories\Sys;

use App\Models\Sys\SysBanners;
use Illuminate\Database\Eloquent\Collection;

class BannersRepository
{
    /**
     * 获取指定位置状态正常的轮播图（仅 id、position、image、link_type、link_value，按 sort、id 升序）
     */
    public function selectByPosition(?string $position): Collection
    {
        return SysBanners::select('id', 'position', 'image', 'link_type', 'link_value')
            ->where('position', $position ?? '')
            ->where('is_show', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }
}
