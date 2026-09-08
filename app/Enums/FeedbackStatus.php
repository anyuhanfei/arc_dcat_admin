<?php

namespace App\Enums;

/**
 * 意见反馈状态枚举
 */
enum FeedbackStatus: int
{
    case PENDING    = 0;
    case PROCESSING = 1;
    case COMPLETED  = 2;
    case ON_HOLD    = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => '待处理',
            self::PROCESSING => '处理中',
            self::COMPLETED  => '处理完成',
            self::ON_HOLD    => '搁置',
        };
    }

    /**
     * 返回 [value => label] 格式的选项数组，用于表单 select 的 options
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * 返回所有 value 的数组
     *
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): int => $case->value, self::cases());
    }
}
