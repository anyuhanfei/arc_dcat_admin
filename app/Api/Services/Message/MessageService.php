<?php

namespace App\Api\Services\Message;

use App\Api\Repositories\Message\MessageRepository;
use App\Enums\MessageTargetType;
use App\Enums\MessageType;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    public function __construct(
        protected MessageRepository $repository,
    ) {}

    /**
     * 未读消息统计（总数 + 按类型分组）
     *
     * @return array{total: int, types: array<int, array{type: int, type_label: string, unread_count: int, latest_title: string, latest_time: string}>}
     */
    public function unreadCount(int $userId): array
    {
        $countByType = $this->repository->getUnreadCountByType($userId);

        $types = [];
        $total = 0;

        foreach (MessageType::values() as $type) {
            $unreadCount = $countByType[$type] ?? 0;
            $total += $unreadCount;

            $latest = $this->repository->getLatestMessageByType($userId, $type);

            $types[] = [
                'type'          => $type,
                'type_label'    => MessageType::from($type)->label(),
                'unread_count'  => $unreadCount,
                'latest_title'  => $latest?->title ?? '',
                'latest_time'   => $latest ? format_relative_time(\Carbon\Carbon::parse($latest->created_at)) : '',
            ];
        }

        return [
            'total' => $total,
            'types' => $types,
        ];
    }

    /**
     * 消息列表（分页）
     */
    public function list(int $userId, int $page, int $perPage, ?int $type = null): LengthAwarePaginator
    {
        return $this->repository->paginateVisibleMessages($userId, $page, $perPage, $type);
    }

    /**
     * 消息详情（自动标记已读）
     */
    public function detail(int $id, int $userId): object
    {
        $message = $this->repository->findVisibleMessage($id, $userId);
        if (! $message) {
            throwBusinessException('消息不存在或无权查看');
        }

        // 标记已读（upsert：不存在则插入，已存在则更新 is_read/read_at）
        $this->repository->createReadRecord($id, $userId, true);

        // 确保返回对象 is_read=1（查询在标记之前）
        $message->is_read = 1;

        return $message;
    }

    /**
     * 全部标记已读（可按类型筛选）
     */
    public function readAll(int $userId, ?int $type = null): void
    {
        $unreadIds = $this->repository->getUnreadMessageIds($userId, $type);
        if (empty($unreadIds)) {
            return;
        }

        // 获取每条消息的 target_type，分组处理
        $targetTypeMap = $this->repository->getTargetTypeMap($unreadIds);

        $customIds = [];
        $allIds = [];

        foreach ($unreadIds as $id) {
            $targetType = $targetTypeMap[$id] ?? MessageTargetType::ALL->value;
            if ($targetType === MessageTargetType::CUSTOM->value) {
                $customIds[] = $id;
            } else {
                $allIds[] = $id;
            }
        }

        // 自定义消息：记录已存在，直接 update
        if (! empty($customIds)) {
            $this->repository->batchUpdateReadRecords($customIds, $userId);
        }

        // 全部用户消息：记录可能不存在，用 upsert
        if (! empty($allIds)) {
            $this->repository->batchInsertReadRecords($allIds, $userId);
        }
    }
}
