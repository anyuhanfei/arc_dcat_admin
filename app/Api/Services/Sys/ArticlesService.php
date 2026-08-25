<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\ArticlesRepository;
use App\Models\Sys\SysArticleCategories;
use App\Models\Sys\SysArticles;
use Illuminate\Support\Collection;

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
        return $this->repository->selectCategories()->map(function (SysArticleCategories $item) {
            return [
                'id'    => $item->id,
                'name'  => $item->name,
                'image' => full_url($item->image),
            ];
        });
    }

    /**
     * 获取文章分页列表（仅发布状态，可按分类过滤）
     *
     * @param int|null $categoryId 分类ID，为空时返回全部分类
     */
    public function index(?int $categoryId, int $perPage): array
    {
        $paginator = $this->repository->paginate($categoryId, $perPage);

        return format_paginated_datas($paginator, ['*'], function (SysArticles $item) {
            return [
                'id'            => $item->id,
                'category_id'   => $item->category_id,
                'category_name' => $item->category->name ?? '',
                'title'         => $item->title,
                'image'         => full_url($item->image),
                'author'        => $item->author,
                'published_at'  => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * 获取文章详情（仅发布状态，不存在或未发布时提示已下架）
     */
    public function show(int $id): array
    {
        $item = $this->repository->findPublishedById($id);

        if ($item === null) {
            throwBusinessException('文章已下架');
        }

        return [
            'id'            => $item->id,
            'category_id'   => $item->category_id,
            'category_name' => $item->category->name ?? '',
            'title'         => $item->title,
            'image'         => full_url($item->image),
            'author'        => $item->author,
            'published_at'  => $item->created_at->format('Y-m-d H:i:s'),
            'summary'       => $item->summary,
            'keywords'      => $item->keywords ? array_values(array_filter(array_map('trim', explode(',', $item->keywords)))) : [],
            'content'       => $item->content,
        ];
    }
}
