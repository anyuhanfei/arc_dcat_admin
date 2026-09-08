<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SysConfigCategories extends Model implements Sortable
{
	use HasDateTimeFormatter;
	use SortableTrait;

    protected $table = 'sys_config_categories';

    protected $fillable = ['name', 'sort'];

    protected $casts = [
        'sort'       => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 拖拽排序：新建时自动分配排序值，无需手动输入
    public $sortable = [
        'order_column_name'  => 'sort',
        'sort_when_creating' => true,
    ];

    /**
     * 分类下的配置项
     */
    public function configs(): HasMany
    {
        return $this->hasMany(SysConfigs::class, 'category_id');
    }
}
