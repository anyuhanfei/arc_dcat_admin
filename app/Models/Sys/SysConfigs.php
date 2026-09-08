<?php

namespace App\Models\Sys;

use App\Enums\ConfigType;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysConfigs extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'sys_configs';

    protected $fillable = [
        'category_id',
        'key',
        'name',
        'type',
        'options',
        'allowed_extensions',
        'value',
        'is_editable',
        'description',
        'sort',
    ];

    protected $casts = [
        'options'    => 'array',
        'is_editable' => 'integer',
        'sort'       => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 所属配置分类
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SysConfigCategories::class, 'category_id');
    }
}
