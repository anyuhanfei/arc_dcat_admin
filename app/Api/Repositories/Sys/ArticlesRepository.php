<?php

namespace App\Api\Repositories\Sys;

use App\Enums\PublishStatus;
use App\Models\Sys\SysArticleCategories;
use App\Models\Sys\SysArticles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticlesRepository
{
    /**
     * 获取所有状态正常的分类（id/name/image，按 sort 升序）
     */
    public function selectCategories(): Collection
    {
        return SysArticleCategories::select('id', 'name', 'image')
            ->where('is_show', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    /**
     * 获取文章分页列表（仅发布状态，可按分类过滤，最新在前）
     */
    public function paginate(?int $categoryId, int $perPage): LengthAwarePaginator
    {
        $query = SysArticles::select('id', 'category_id', 'title', 'image', 'author', 'created_at')
            ->with('category:id,name')
            ->where('status', PublishStatus::PUBLISHED->value)
            ->orderBy('id', 'desc');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query->paginate($perPage);
    }

    /**
     * 按 ID 获取已发布文章详情（不存在或未发布返回 null）
     */
    public function findPublishedById(int $id): ?SysArticles
    {
        return SysArticles::with('category:id,name')
            ->where('id', $id)
            ->where('status', PublishStatus::PUBLISHED->value)
            ->first();
    }
}
