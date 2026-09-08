<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\ArticlesRepository;
use App\Models\Sys\SysArticles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticlesService
{
    public function __construct(
        protected ArticlesRepository $repository,
    ) {}

    /**
     * 获取文章分类列表（仅状态正常，按 sort 升序）
     */
    public function categories(): Collection
    {
        return $this->repository->selectCategories();
    }

    /**
     * 获取文章分页列表（仅发布状态，可按分类过滤）
     *
     * @param int|null $categoryId 分类ID，为空时返回全部分类
     */
    public function index(?int $categoryId, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginate($categoryId, $perPage);
    }

    /**
     * 获取文章详情（仅发布状态，不存在或未发布时提示已下架）
     *
     * @throws \App\Exceptions\BusinessException
     */
    public function show(int $id): SysArticles
    {
        $item = $this->repository->findPublishedById($id);

        if ($item === null) {
            throwBusinessException('文章已下架');
        }

        return $item;
    }
}
