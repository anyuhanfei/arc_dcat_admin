<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SysAgreements extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'sys_agreements';

    protected $fillable = ['title', 'content'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
