<?php

namespace App\Api\Repositories\Feedback;

use App\Models\User\UserFeedback;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedbackRepository
{
    public function create(array $data): UserFeedback
    {
        return UserFeedback::create($data);
    }

    public function paginateByUserId(int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        return UserFeedback::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findByIdAndUserId(int $id, int $userId): ?UserFeedback
    {
        return UserFeedback::where('id', $id)->where('user_id', $userId)->first();
    }
}
