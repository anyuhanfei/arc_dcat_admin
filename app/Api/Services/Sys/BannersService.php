<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\BannersRepository;
use App\Models\Sys\SysBanners;
use Illuminate\Support\Collection;

class BannersService
{
    public function __construct(
        protected BannersRepository $repository,
    ) {}

    /**
     * 获取指定位置的轮播图列表（仅状态正常，按 sort 升序）
     *
     * @param string|null $position 位置标识（config('business.banner.positions') 的 key）；
     *                              为空时取配置的第一个（唯一）位置，配置为空数组时退化为 ''
     */
    public function list(?string $position): Collection
    {
        $position = $position ?: (string) (array_key_first(config('business.banner.positions')) ?? '');

        return $this->repository->selectByPosition($position)->map(function (SysBanners $item) {
            return [
                'id'             => $item->id,
                'position'       => $item->position,
                'position_name'  => config('business.banner.positions.'.$item->position.'.name.'.app()->getLocale(), $item->position),
                'image'          => full_url($item->image),
                'link_type'      => $item->link_type,
                'link_type_name' => config('business.banner.link_types.'.$item->link_type.'.name.'.app()->getLocale(), $item->link_type),
                'link_value'     => $item->link_value,
            ];
        });
    }
}
