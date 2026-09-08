<?php

namespace App\Api\Services\Feedback;

use App\Api\Repositories\Feedback\FeedbackRepository;
use App\Enums\FeedbackStatus;
use App\Models\User\UserFeedback;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedbackService
{
    public function __construct(
        protected FeedbackRepository $repository,
    ) {}

    public function submit(array $data, int $userId): UserFeedback
    {
        $data['user_id'] = $userId;
        $data['status'] = FeedbackStatus::PENDING->value;
        return $this->repository->create($data);
    }

    public function list(int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateByUserId($userId, $page, $perPage);
    }

    public function detail(int $id, int $userId): UserFeedback
    {
        $feedback = $this->repository->findByIdAndUserId($id, $userId);
        if (! $feedback) {
            throwBusinessException('反馈记录不存在或无权查看');
        }
        return $feedback;
    }
}
