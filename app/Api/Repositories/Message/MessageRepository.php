<?php

namespace App\Api\Repositories\Message;

use App\Enums\MessageStatus;
use App\Enums\MessageTargetType;
use App\Models\User\UserMessageRead;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MessageRepository
{
    /**
     * 构建用户可见消息的基础查询（混合查询：DB Query Builder + LEFT JOIN）
     *
     * 可见性条件：status=SENT AND (target_type=ALL OR (target_type=CUSTOM AND r.user_id IS NOT NULL))
     */
    public function getVisibleMessagesQuery(int $userId): Builder
    {
        return DB::table('user_messages as m')
            ->leftJoin('user_message_reads as r', function ($join) use ($userId) {
                $join->on('m.id', '=', 'r.message_id')
                    ->where('r.user_id', '=', $userId);
            })
            ->select('m.*', DB::raw('COALESCE(r.is_read, 0) as is_read'))
            ->where('m.status', MessageStatus::SENT->value)
            ->where(function ($q) {
                $q->where('m.target_type', MessageTargetType::ALL->value)
                    ->orWhere(function ($q2) {
                        $q2->where('m.target_type', MessageTargetType::CUSTOM->value)
                            ->whereNotNull('r.user_id');
                    });
            });
    }

    /**
     * 分页查询用户可见消息
     */
    public function paginateVisibleMessages(int $userId, int $page, int $perPage, ?int $type = null): LengthAwarePaginator
    {
        $query = $this->getVisibleMessagesQuery($userId);

        if ($type !== null) {
            $query->where('m.type', $type);
        }

        return $query->orderBy('m.created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 查询单条可见消息
     */
    public function findVisibleMessage(int $id, int $userId): ?object
    {
        return $this->getVisibleMessagesQuery($userId)
            ->where('m.id', $id)
            ->first();
    }

    /**
     * 获取未读消息ID列表
     */
    public function getUnreadMessageIds(int $userId, ?int $type = null): array
    {
        $query = $this->getVisibleMessagesQuery($userId)
            ->where(DB::raw('COALESCE(r.is_read, 0)'), 0);

        if ($type !== null) {
            $query->where('m.type', $type);
        }

        return $query->pluck('m.id')->all();
    }

    /**
     * 按类型统计未读数，返回 [type => count]
     */
    public function getUnreadCountByType(int $userId): array
    {
        $rows = $this->getVisibleMessagesQuery($userId)
            ->where(DB::raw('COALESCE(r.is_read, 0)'), 0)
            ->groupBy('m.type')
            ->select('m.type', DB::raw('COUNT(*) as count'))
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->type] = (int) $row->count;
        }
        return $result;
    }

    /**
     * 获取指定类型的最新一条消息
     */
    public function getLatestMessageByType(int $userId, int $type): ?object
    {
        return $this->getVisibleMessagesQuery($userId)
            ->where('m.type', $type)
            ->orderBy('m.created_at', 'desc')
            ->first();
    }

    /**
     * 根据消息ID列表获取 target_type 映射，返回 [id => target_type]
     */
    public function getTargetTypeMap(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table('user_messages')
            ->whereIn('id', $ids)
            ->select('id', 'target_type')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->id] = (int) $row->target_type;
        }
        return $map;
    }

    /**
     * 创建/更新单条已读记录（upsert 避免唯一索引冲突）
     */
    public function createReadRecord(int $messageId, int $userId, bool $isRead = true): void
    {
        UserMessageRead::upsert(
            [
                [
                    'message_id' => $messageId,
                    'user_id'    => $userId,
                    'is_read'    => $isRead ? 1 : 0,
                    'read_at'    => now(),
                ],
            ],
            ['message_id', 'user_id'],
            ['is_read', 'read_at']
        );
    }

    /**
     * 批量更新自定义消息的已读状态（记录已存在，直接 update）
     */
    public function batchUpdateReadRecords(array $messageIds, int $userId): void
    {
        if (empty($messageIds)) {
            return;
        }

        UserMessageRead::where('user_id', $userId)
            ->whereIn('message_id', $messageIds)
            ->update(['is_read' => 1, 'read_at' => now()]);
    }

    /**
     * 批量插入全部用户消息的已读记录（记录可能不存在，用 upsert）
     */
    public function batchInsertReadRecords(array $messageIds, int $userId): void
    {
        if (empty($messageIds)) {
            return;
        }

        $data = array_map(function (int $id) use ($userId): array {
            return [
                'message_id' => $id,
                'user_id'    => $userId,
                'is_read'    => 1,
                'read_at'    => now(),
            ];
        }, $messageIds);

        UserMessageRead::upsert($data, ['message_id', 'user_id'], ['is_read', 'read_at']);
    }
}
