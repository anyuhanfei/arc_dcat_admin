<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\FaqRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FaqService
{
    public function __construct(
        protected FaqRepository $repository,
    ) {}

    /**
     * 获取常见问题分类列表
     */
    public function categories(): Collection
    {
        return $this->repository->selectAll();
    }

    /**
     * 获取常见问题分页列表
     *
     * @param int|null $categoryId 分类ID，为空时返回全部分类
     */
    public function questions(?int $categoryId, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginate($categoryId, $perPage);
    }
}
