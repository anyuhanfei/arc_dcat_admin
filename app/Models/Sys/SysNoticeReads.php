<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SysNoticeReads extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'sys_notice_reads';

    /**
     * 已读用户
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * 所属公告
     */
    public function notice()
    {
        return $this->belongsTo(\App\Models\Sys\SysNotices::class, 'notice_id');
    }
    
}
