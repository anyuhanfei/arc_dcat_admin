<?php

namespace App\Models\Sys;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysArticles extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'sys_articles';

    // 文章状态枚举（后台展示用语言文件，此处为 fallback）
    public const STATUS_DRAFT = 0;
    public const STATUS_PUBLISHED = 1;
    public const STATUS_MAP = [
        self::STATUS_DRAFT => '草稿',
        self::STATUS_PUBLISHED => '发布',
    ];

    /**
     * 所属文章分类
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SysArticleCategories::class, 'category_id');
    }
    
}
