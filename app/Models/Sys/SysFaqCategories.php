<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SysFaqCategories extends Model implements Sortable
{
	use HasDateTimeFormatter;
	use SortableTrait;

    protected $table = 'sys_faq_categories';

    // 拖拽排序：新建时自动分配排序值，无需手动输入
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    // 显示状态枚举映射
    public const IS_SHOW = [0 => '隐藏', 1 => '显示'];
    
}
