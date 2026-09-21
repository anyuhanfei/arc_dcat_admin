<?php

namespace App\Models\User;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMessageRead extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'user_message_reads';

    protected $fillable = ['message_id', 'user_id', 'is_read', 'read_at'];

    protected $casts = [
        'is_read' => 'integer',
        'read_at' => 'datetime',
    ];

    /**
     * 所属消息
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(UserMessage::class, 'message_id');
    }

    /**
     * 所属用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}
