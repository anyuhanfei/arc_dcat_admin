<?php

namespace App\Models\Sys;

use App\Enums\PublishStatus;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysArticles extends Model
{
	use HasDateTimeFormatter;
	use SoftDeletes;

    protected $table = 'sys_articles';

    protected $fillable = ['category_id', 'title', 'author', 'image', 'summary', 'keywords', 'content', 'status'];

    protected $casts = [
        'status' => PublishStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 所属文章分类
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SysArticleCategories::class, 'category_id');
    }
}
