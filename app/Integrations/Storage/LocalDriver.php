<?php

namespace App\Integrations\Storage;

/**
 * 本地存储：落盘 public/uploads，URL 走站点域名
 */
class LocalDriver extends AbstractDriver
{
    public function putFile(string $localAbsPath, string $relativeKey): void
    {
        $key = $this->normalizeKey($relativeKey);
        $target = public_path('uploads/' . $key);

        $dir = dirname($target);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        copy($localAbsPath, $target);
    }

    public function url(string $relativeKey): string
    {
        return full_url($this->normalizeKey($relativeKey));
    }

    public function delete(string $relativeKey): void
    {
        $key = $this->normalizeKey($relativeKey);
        $path = public_path('uploads/' . $key);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function listKeys(string $prefix = ''): array
    {
        $root = public_path('uploads');
        $prefix = trim($prefix, '/');
        $out = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $rel = ltrim(substr($file->getPathname(), strlen($root)), '/');
            if ($prefix !== '' && ! str_starts_with($rel, $prefix.'/')) {
                continue;
            }
            $out[] = $this->normalizeKey($rel);
        }

        return $out;
    }

    public function driver(): string
    {
        return 'none';
    }
}
