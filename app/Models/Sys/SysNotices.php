<?php

namespace App\Models\Sys;

use App\Enums\PublishStatus;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SysNotices extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'sys_notices';

    protected $fillable = ['title', 'image', 'content', 'status'];

    protected $casts = [
        'status' => PublishStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
