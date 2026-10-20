<?php

namespace App\Integrations\Storage;

/**
 * 存储驱动工厂：按 config('services.storage.default') 返回对应驱动单例
 * none=本地，cos=腾讯云，aliyun=阿里云
 */
class StorageManager
{
    private static ?StorageDriver $instance = null;

    public static function drive(): StorageDriver
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        return self::$instance = match (config('services.storage.default', 'none')) {
            'cos'     => new TencentCos(config('services.storage.cos', [])),
            'aliyun'  => new AliyunOss(config('services.storage.aliyun', [])),
            default   => new LocalDriver(),
        };
    }

    /**
     * 当前是否启用了云存储（非本地）
     */
    public static function isCloud(): bool
    {
        return self::drive()->driver() !== 'none';
    }
}
