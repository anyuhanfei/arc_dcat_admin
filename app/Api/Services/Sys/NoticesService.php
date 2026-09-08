<?php

namespace App\Api\Services\Sys;

use App\Api\Repositories\Sys\NoticesRepository;
use App\Models\Sys\SysNotices;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class NoticesService
{
    public function __construct(
        protected NoticesRepository $repository,
    ) {}

    /**
     * 获取已发布公告分页列表（最新在前）
     */
    public function index(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginatePublished($perPage);
    }

    /**
     * 获取公告详情（仅发布状态，不存在或未发布时提示已下架；已登录用户记录已读）
     *
     * @throws \App\Exceptions\BusinessException
     */
    public function show(int $id, ?User $user): SysNotices
    {
        $item = $this->repository->findPublishedById($id);

        if ($item === null) {
            throwBusinessException('公告已下架');
        }

        if ($user !== null) {
            $this->repository->markAsRead($item->id, $user->id);
        }

        return $item;
    }

    /**
     * 获取最新公告（已登录用户仅返回未读公告，查不到返回 null）
     */
    public function latest(?User $user): ?SysNotices
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

        return $item;
    }
}
