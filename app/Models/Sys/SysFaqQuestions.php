<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SysFaqQuestions extends Model implements Sortable
{
	use HasDateTimeFormatter;
	use SortableTrait;
	use SoftDeletes;

    protected $table = 'sys_faq_questions';

    protected $fillable = ['category_id', 'question', 'answer', 'sort', 'is_show'];

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

    /**
     * 所属分类
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SysFaqCategories::class, 'category_id');
    }
}
