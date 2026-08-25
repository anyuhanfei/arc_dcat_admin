<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\NoticesRepository;
use App\Models\Sys\SysNotices;
use App\Models\User;

class NoticesService
{
    public function __construct(
        protected NoticesRepository $repository,
    ) {}

    /**
     * 获取已发布公告分页列表（最新在前）
     */
    public function index(int $perPage): array
    {
        $paginator = $this->repository->paginatePublished($perPage);

        return format_paginated_datas($paginator, ['*'], function (SysNotices $item) {
            return [
                'id'           => $item->id,
                'title'        => $item->title,
                'image'        => full_url($item->image),
                'published_at' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * 获取公告详情（仅发布状态，不存在或未发布时提示已下架；已登录用户记录已读）
     */
    public function show(int $id, ?User $user): array
    {
        $item = $this->repository->findPublishedById($id);

        if ($item === null) {
            throwBusinessException('公告已下架');
        }

        if ($user !== null) {
            $this->repository->markAsRead($item->id, $user->id);
        }

        return $this->formatDetail($item);
    }

    /**
     * 获取最新公告（已登录用户仅返回未读公告，查不到返回 null）
     */
    public function latest(?User $user): ?array
    {
        $item = $user !== null
            ? $this->repository->latestUnreadPublished($user->id)
            : $this->repository->latestPublished();

        if ($item === null) {
            return null;
        }

        if ($user !== null) {
            $this->repository->markAsRead($item->id, $user->id);
        }

        return $this->formatDetail($item);
    }

    /**
     * 公告详情返回结构
     */
    private function formatDetail(SysNotices $item): array
    {
        return [
            'id'           => $item->id,
            'title'        => $item->title,
            'image'        => full_url($item->image),
            'published_at' => $item->created_at->format('Y-m-d H:i:s'),
            'content'      => $item->content,
        ];
    }
}
