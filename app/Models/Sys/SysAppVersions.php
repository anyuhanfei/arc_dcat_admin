<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SysAppVersions extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'sys_app_versions';

    protected $fillable = ['platform', 'version', 'version_code', 'description', 'wgt', 'apk', 'store_url', 'force_update', 'is_show', 'sort'];

    protected $casts = [
        'version_code' => 'integer',
        'force_update' => 'boolean',
        'is_show' => 'boolean',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
