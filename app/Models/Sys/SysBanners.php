<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SysBanners extends Model implements Sortable
{
	use HasDateTimeFormatter;
	use SortableTrait;

    protected $table = 'sys_banners';

    // 拖拽排序：新建时自动分配排序值，无需手动输入
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_show' => 'boolean',
        ];
    }
}
