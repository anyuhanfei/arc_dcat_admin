<?php

namespace App\Api\Services\Sys;

use App\Integrations\Storage\StorageDriver;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * 文件上传服务：本地 / 云存储统一落盘，视频额外截取首帧封面
 */
class UploadService
{
    private StorageDriver $storage;

    public function __construct()
    {
        $this->storage = cloud_storage();
    }

    /**
     * 处理上传文件，返回相对路径、完整URL；视频额外返回封面相对路径与完整URL
     *
     * @return array{path:string,url:string,type:string,ext:string,size:int,cover_path?:string,cover_url?:string}
     */
    public function handle(UploadedFile $file): array
    {
        $ext = strtolower((string) $file->getClientOriginalExtension() ?: $file->extension());
        $type = $this->resolveType($ext);

        // 相对键：Ymd/Ymd_His_随机8位.ext（与后台媒体命名风格一致）
        $key = date('Ymd') . '/' . date('Ymd_His') . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;
        $source = $file->getRealPath();

        $this->storage->putFile($source, $key);

        $result = [
            'path' => $key,
            'url'  => $this->storage->url($key),
            'type' => $type,
            'ext'  => $ext,
            'size' => $file->getSize(),
        ];

        // 视频：第1秒截取一帧作为同名 jpg 封面
        if ($type === 'video') {
            $coverKey = $this->coverKey($key);
            $coverTmp = $this->captureFrame($source, $coverKey);
            try {
                $this->storage->putFile($coverTmp, $coverKey);
                $result['cover_path'] = $coverKey;
                $result['cover_url']  = $this->storage->url($coverKey);
            } finally {
                if (is_file($coverTmp)) {
                    @unlink($coverTmp);
                }
            }
        }

        return $result;
    }

    /**
     * 按扩展名归类：image / video / doc
     */
    private function resolveType(string $ext): string
    {
        if (in_array($ext, config('cloud.upload.image_ext', []), true)) {
            return 'image';
        }
        if (in_array($ext, config('cloud.upload.video_ext', []), true)) {
            return 'video';
        }
        return 'doc';
    }

    /**
     * 视频键 → 同名 jpg 封面键（同目录，替换扩展名为 jpg）
     */
    private function coverKey(string $videoKey): string
    {
        $dir = dirname($videoKey);
        $base = pathinfo($videoKey, PATHINFO_FILENAME);
        return ($dir === '.' ? '' : $dir . '/') . $base . '.jpg';
    }

    /**
     * ffmpeg 第1秒截取一帧到临时 jpg，返回临时文件路径
     */
    private function captureFrame(string $videoSource, string $coverKey): string
    {
        $bin = (string) config('cloud.upload.ffmpeg_bin', '/usr/local/bin/ffmpeg');
        $tmp = sys_get_temp_dir() . '/cover_' . \Illuminate\Support\Str::random(8) . '.jpg';

        $process = new Process([
            $bin,
            '-ss', '1',
            '-i', $videoSource,
            '-frames:v', '1',
            '-q:v', '2',
            '-y',
            $tmp,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($tmp) || filesize($tmp) === 0) {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
            throw new \RuntimeException('视频封面截取失败: ' . trim($process->getErrorOutput()));
        }

        return $tmp;
    }
}
