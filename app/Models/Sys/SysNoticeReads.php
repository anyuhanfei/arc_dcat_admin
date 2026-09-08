<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysNoticeReads extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'sys_notice_reads';

    protected $fillable = ['notice_id', 'user_id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 已读用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * 所属公告
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Sys\SysNotices::class, 'notice_id');
    }
}
