<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SysAppVersions extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'sys_app_versions';
    
}
