<?php

namespace App\Integrations\Storage;

/**
 * 存储驱动统一契约：本地 / 腾讯云COS / 阿里云OSS
 *
 * 对外只使用“相对键 relativeKey”（如 Ymd/xxx.jpg），由具体驱动决定落地位置；
 * url() 返回可直接访问的完整地址。
 */
interface StorageDriver
{
    /**
     * 将本地临时文件上传/落盘到存储，relativeKey 为最终相对键
     *
     * @param string $localAbsPath 本地临时文件绝对路径
     * @param string $relativeKey  目标相对键（含目录，如 Ymd/xxx.jpg）
     */
    public function putFile(string $localAbsPath, string $relativeKey): void;

    /**
     * 由相对键生成可访问的完整 URL
     */
    public function url(string $relativeKey): string;

    /**
     * 删除指定相对键对应的对象/文件
     */
    public function delete(string $relativeKey): void;

    /**
     * 递归列举指定前缀下的相对键（供媒体库列表使用），返回清洗后的相对键数组
     *
     * @return array<int, string>
     */
    public function listKeys(string $prefix = ''): array;

    /**
     * 当前驱动标识：none | cos | aliyun
     */
    public function driver(): string;
}
