<?php

namespace App\Models\User;

use App\Enums\FeedbackStatus;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFeedback extends Model
{
	use HasDateTimeFormatter;
	use SoftDeletes;

    protected $table = 'user_feedbacks';

    protected $fillable = ['user_id', 'title', 'content', 'images', 'contact_name', 'contact_phone', 'status', 'handler_id', 'handled_at', 'reply', 'replied_at', 'admin_remark'];

    protected $casts = [
        'images'     => 'array',
        'status'     => FeedbackStatus::class,
        'handled_at' => 'datetime',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 提交用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}
