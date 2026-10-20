<?php

namespace App\Integrations\Storage;

/**
 * 存储驱动公共能力：相对键清洗、域名拼接
 */
abstract class AbstractDriver implements StorageDriver
{
    /**
     * 清洗相对键：去前导斜杠、反斜杠转正、剥离目录跳转，防止路径穿越
     */
    protected function normalizeKey(string $key): string
    {
        $key = str_replace('\\', '/', $key);
        $parts = [];
        foreach (explode('/', $key) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $segment;
        }
        return implode('/', $parts);
    }

    /**
     * 用基础域名 + 相对键拼完整 URL
     */
    protected function joinUrl(string $domain, string $key): string
    {
        $domain = rtrim((string) $domain, '/');
        return $domain === '' ? '/' . ltrim($key, '/') : $domain . '/' . ltrim($key, '/');
    }
}
