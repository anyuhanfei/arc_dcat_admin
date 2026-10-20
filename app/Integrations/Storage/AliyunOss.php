<?php

namespace App\Integrations\Storage;

use OSS\OssClient;
use Throwable;

/**
 * 阿里云 OSS 封装（公有读 + 自定义域名，不签名）
 * 官方 SDK：aliyuncs/oss-sdk-php；配置见 config/services.php storage.aliyun
 */
class AliyunOss extends AbstractDriver
{
    private OssClient $client;

    private string $bucket;

    private string $domain;

    private string $prefix;

    public function __construct(array $config)
    {
        $this->bucket = (string) ($config['bucket'] ?? '');
        $this->domain = (string) ($config['domain'] ?? '');
        $this->prefix = trim((string) ($config['prefix'] ?? ''), '/');

        $this->client = new OssClient(
            (string) ($config['access_key_id'] ?? ''),
            (string) ($config['access_key_secret'] ?? ''),
            (string) ($config['endpoint'] ?? ''),
            false
        );
    }

    /**
     * 对象键 = 前缀 + 相对键
     */
    private function objectKey(string $key): string
    {
        $key = $this->normalizeKey($key);
        return $this->prefix === '' ? $key : $this->prefix . '/' . $key;
    }

    public function putFile(string $localAbsPath, string $relativeKey): void
    {
        $key = $this->objectKey($relativeKey);

        try {
            $this->client->uploadFile($this->bucket, $key, $localAbsPath);
        } catch (Throwable $e) {
            throw new \RuntimeException('阿里云OSS上传失败: ' . $e->getMessage(), 0, $e);
        }
    }

    public function url(string $relativeKey): string
    {
        return $this->joinUrl($this->domain, $this->objectKey($relativeKey));
    }

    public function delete(string $relativeKey): void
    {
        try {
            $this->client->deleteObject($this->bucket, $this->objectKey($relativeKey));
        } catch (Throwable $e) {
            throw new \RuntimeException('阿里云OSS删除失败: ' . $e->getMessage(), 0, $e);
        }
    }

    public function listKeys(string $prefix = ''): array
    {
        $prefix = $this->normalizeKey($prefix);
        $listPrefix = $this->prefix === '' ? $prefix : ($prefix === '' ? $this->prefix : $this->prefix . '/' . $prefix);

        $out = [];
        $marker = '';

        do {
            $result = $this->client->listObjects($this->bucket, [
                'prefix'    => $listPrefix,
                'marker'    => $marker,
                'max-keys'  => 1000,
                'delimiter' => '', // 空分隔符，递归列举子目录
            ]);

            foreach ($result->getObjectList() ?? [] as $object) {
                $key = $object->getKey();
                if ($key === '' || str_ends_with($key, '/')) {
                    continue;
                }
                $out[] = $this->stripPrefix($key);
            }

            $marker = $result->getNextMarker();
            $truncated = strtolower((string) $result->getIsTruncated()) === 'true';
        } while ($truncated && $marker !== '');

        return $out;
    }

    /**
     * 去掉对象键上的云配置前缀，还原为相对键
     */
    private function stripPrefix(string $key): string
    {
        if ($this->prefix !== '' && str_starts_with($key, $this->prefix . '/')) {
            $key = substr($key, strlen($this->prefix) + 1);
        }
        return $this->normalizeKey($key);
    }

    public function driver(): string
    {
        return 'aliyun';
    }
}
