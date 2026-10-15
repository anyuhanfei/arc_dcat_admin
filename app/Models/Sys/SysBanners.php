<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SysBanners extends Model implements Sortable
{
	use HasDateTimeFormatter;
	use SortableTrait;
	use SoftDeletes;

    protected $table = 'sys_banners';

    protected $fillable = ['position', 'image', 'link_type', 'link_value', 'sort', 'is_show'];

    protected $casts = [
        'is_show' => 'boolean',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 拖拽排序：新建时自动分配排序值，无需手动输入
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];
}
