<?php

namespace App\Api\Repositories\Sys;

use App\Enums\PublishStatus;
use App\Models\Sys\SysNoticeReads;
use App\Models\Sys\SysNotices;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NoticesRepository
{
    /**
     * 获取已发布公告分页列表（最新在前）
     */
    public function paginatePublished(int $perPage): LengthAwarePaginator
    {
        return SysNotices::select('id', 'title', 'image', 'created_at')
            ->where('status', PublishStatus::PUBLISHED->value)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * 按 ID 获取已发布公告详情（不存在或未发布返回 null）
     */
    public function findPublishedById(int $id): ?SysNotices
    {
        return SysNotices::where('id', $id)
            ->where('status', PublishStatus::PUBLISHED->value)
            ->first();
    }

    /**
     * 获取最近发布的一条公告（不存在或未发布返回 null）
     */
    public function latestPublished(): ?SysNotices
    {
        return SysNotices::where('status', PublishStatus::PUBLISHED->value)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * 获取该用户最近一条未读的已发布公告
     */
    public function latestUnreadPublished(int $userId): ?SysNotices
    {
        return SysNotices::where('status', PublishStatus::PUBLISHED->value)
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('sys_notice_reads')
                    ->whereColumn('sys_notice_reads.notice_id', 'sys_notices.id')
                    ->where('user_id', $userId);
            })
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * 记录公告已读（唯一约束保证不重复）
     */
    public function markAsRead(int $noticeId, int $userId): void
    {
        SysNoticeReads::firstOrCreate([
            'notice_id' => $noticeId,
            'user_id'   => $userId,
        ]);
    }
}
