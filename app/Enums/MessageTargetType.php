<?php

namespace App\Enums;

/**
 * 消息目标用户类型枚举
 */
enum MessageTargetType: int
{
    case CUSTOM = 0;
    case ALL    = 1;

    public function label(): string
    {
        return match ($this) {
            self::CUSTOM => '自定义选择',
            self::ALL    => '全部用户',
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
