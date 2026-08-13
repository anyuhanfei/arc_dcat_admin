<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SysNotices extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'sys_notices';

    // 公告状态枚举（后台展示用语言文件，此处为 fallback）
    public const STATUS_DRAFT = 0;
    public const STATUS_PUBLISHED = 1;
    public const STATUS_MAP = [
        self::STATUS_DRAFT => '草稿',
        self::STATUS_PUBLISHED => '发布',
    ];
    
}
