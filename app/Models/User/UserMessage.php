<?php

namespace App\Models\User;

use App\Enums\MessageStatus;
use App\Enums\MessageTargetType;
use App\Enums\MessageType;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMessage extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'user_messages';

    protected $fillable = ['type', 'title', 'image', 'content', 'target_type', 'status', 'is_system_generated', 'relation_type', 'relation_id', 'relation_user_id'];

    protected $casts = [
        'type'                => MessageType::class,
        'target_type'         => MessageTargetType::class,
        'status'              => MessageStatus::class,
        'is_system_generated' => 'integer',
    ];

    /**
     * 消息已读记录
     */
    public function reads(): HasMany
    {
        return $this->hasMany(UserMessageRead::class, 'message_id');
    }
}
