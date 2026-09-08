<?php

namespace App\Enums;

/**
 * 通用发布状态枚举（草稿/发布）
 * 适用于文章、公告等需要草稿/发布状态的业务
 */
enum PublishStatus: int
{
    case DRAFT = 0;
    case PUBLISHED = 1;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => '草稿',
            self::PUBLISHED => '发布',
        };
    }
}
