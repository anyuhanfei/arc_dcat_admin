<?php

namespace App\Api\Repositories\Sys;

use App\Models\Sys\SysFaqCategories;
use App\Models\Sys\SysFaqQuestions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FaqRepository
{
    /**
     * 获取所有状态正常的分类（仅 id 和 name，按 sort 升序）
     */
    public function selectAll(): Collection
    {
        return SysFaqCategories::select('id', 'name')
            ->where('is_show', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    /**
     * 获取常见问题分页列表（仅状态正常，可按分类过滤）
     */
    public function paginate(?int $categoryId, int $perPage): LengthAwarePaginator
    {
        $query = SysFaqQuestions::select('id', 'category_id', 'question', 'answer')
            ->with('category:id,name')
            ->where('is_show', 1)
            ->orderBy('sort')
            ->orderBy('id');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query->paginate($perPage);
    }
}
