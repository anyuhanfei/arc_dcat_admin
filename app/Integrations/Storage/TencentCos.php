<?php

namespace App\Integrations\Storage;

use Qcloud\Cos\Client;
use Throwable;

/**
 * 腾讯云 COS 封装（公有读 + 自定义域名，不签名）
 * 官方 SDK：qcloud/cos-sdk-v5；配置见 config/services.php storage.cos
 */
class TencentCos extends AbstractDriver
{
    private Client $client;

    private string $bucket;

    private string $domain;

    private string $prefix;

    public function __construct(array $config)
    {
        $this->bucket = (string) ($config['bucket'] ?? '');
        $this->domain = (string) ($config['domain'] ?? '');
        $this->prefix = trim((string) ($config['prefix'] ?? ''), '/');

        $this->client = new Client([
            'region'           => (string) ($config['region'] ?? 'ap-beijing'),
            'scheme'           => 'https',
            'timeout'          => 10,
            'connect_timeout'  => 10,
            'retry'            => 3,
            'credentials'      => [
                'secretId'  => (string) ($config['secret_id'] ?? ''),
                'secretKey' => (string) ($config['secret_key'] ?? ''),
            ],
        ]);
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
        $handle = @fopen($localAbsPath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('无法打开本地临时文件: ' . $localAbsPath);
        }

        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => $handle,
            ]);
        } catch (Throwable $e) {
            throw new \RuntimeException('腾讯云COS上传失败: ' . $e->getMessage(), 0, $e);
        } finally {
            // SDK 可能已消费并关闭流，关闭前先确认仍是有效资源
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function url(string $relativeKey): string
    {
        return $this->joinUrl($this->domain, $this->objectKey($relativeKey));
    }

    public function delete(string $relativeKey): void
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->objectKey($relativeKey),
            ]);
        } catch (Throwable $e) {
            throw new \RuntimeException('腾讯云COS删除失败: ' . $e->getMessage(), 0, $e);
        }
    }

    public function listKeys(string $prefix = ''): array
    {
        $prefix = $this->normalizeKey($prefix);
        // 列举前缀 = 云配置前缀 + 入参前缀
        $listPrefix = $this->prefix === '' ? $prefix : ($prefix === '' ? $this->prefix : $this->prefix . '/' . $prefix);

        $out = [];
        $marker = '';

        do {
            $args = [
                'Bucket'  => $this->bucket,
                'Prefix'  => $listPrefix === '' ? null : $listPrefix,
                'Marker'  => $marker,
                'MaxKeys' => 1000,
            ];
            $result = $this->client->listObjects(array_filter($args, fn ($v) => $v !== null));

            foreach ($result['Contents'] ?? [] as $content) {
                $key = $content['Key'] ?? '';
                // 排除目录占位对象（以 / 结尾）
                if ($key === '' || str_ends_with($key, '/')) {
                    continue;
                }
                $out[] = $this->stripPrefix($key);
            }

            $marker = $result['NextMarker'] ?? '';
            $truncated = (bool) ($result['IsTruncated'] ?? false);
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
        return 'cos';
    }
}
