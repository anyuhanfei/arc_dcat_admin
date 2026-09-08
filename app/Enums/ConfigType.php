<?php

namespace App\Enums;

/**
 * 系统配置项表单类型枚举
 */
enum ConfigType: string
{
    case TEXT     = 'text';
    case NUMBER   = 'number';
    case TEXTAREA = 'textarea';
    case EDITOR   = 'editor';
    case RADIO    = 'radio';
    case CHECKBOX = 'checkbox';
    case SELECT   = 'select';
    case IMAGE    = 'image';
    case FILE     = 'file';
    case PASSWORD = 'password';
    case DATE     = 'date';
    case TIME     = 'time';
    case DATETIME = 'datetime';

    public function label(): string
    {
        return match ($this) {
            self::TEXT     => '文本框',
            self::NUMBER   => '数字框',
            self::TEXTAREA => '多行文本框',
            self::EDITOR   => '富文本框',
            self::RADIO    => '单选框',
            self::CHECKBOX => '多选框',
            self::SELECT   => '下拉框',
            self::IMAGE    => '图片上传',
            self::FILE     => '文件上传',
            self::PASSWORD => '密码框',
            self::DATE     => '日期框',
            self::TIME     => '时间框',
            self::DATETIME => '日期时间框',
        };
    }

    /**
     * 返回 [value => label] 格式的选项数组，用于表单 select 的 options
     *
     * @return array<string, string>
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
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
