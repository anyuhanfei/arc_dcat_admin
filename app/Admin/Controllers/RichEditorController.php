<?php

namespace App\Admin\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 富文本（wangEditor）统一上传控制器：图片 / 视频 / 音频.
 *
 * 约定响应：{"code":0,"data":{"url":"完整URL","path":"相对路径","name":"文件名","type":"image|video|audio"}}
 * - 失败：{"code":1,"message":"错误信息"}
 */
class RichEditorController
{
    /** 各类型允许的扩展名 */
    private const ALLOWED_EXT = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'video' => ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'],
    ];

    /** 各类型大小限制（字节） */
    private const MAX_SIZE = [
        'image' => 200 * 1024 * 1024,  // 200MB
        'video' => 200 * 1024 * 1024,  // 200MB
        'audio' => 200 * 1024 * 1024,  // 200MB
    ];

    /**
     * 统一上传入口：根据文件 MIME 自动判断 image / video / audio.
     */
    public function upload(Request $request): JsonResponse
    {
        $file = $request->file('file');

        if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
            Log::warning('[rich-editor] 上传失败：未收到有效文件', [
                'has_file' => $request->hasFile('file'),
                'error' => $file?->getError(),
            ]);

            return response()->json(['code' => 1, 'message' => '请选择要上传的文件']);
        }

        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType();
        $size = $file->getSize();
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        Log::info('[rich-editor] 收到上传请求', [
            'original_name' => $originalName,
            'mime' => $mime,
            'size' => $size,
            'ext' => $ext,
        ]);

        // 根据 MIME 判断类型
        $type = $this->detectType($mime, $ext);
        if ($type === null) {
            Log::warning('[rich-editor] 上传失败：不支持的文件类型', [
                'original_name' => $originalName,
                'mime' => $mime,
                'ext' => $ext,
            ]);

            return response()->json(['code' => 1, 'message' => '不支持的文件类型（仅支持图片/视频/音频）']);
        }

        // 大小校验
        $maxSize = self::MAX_SIZE[$type];
        if ($size > $maxSize) {
            Log::warning('[rich-editor] 上传失败：文件过大', [
                'original_name' => $originalName,
                'type' => $type,
                'size' => $size,
                'max_size' => $maxSize,
            ]);

            return response()->json([
                'code' => 1,
                'message' => sprintf('%s 大小不能超过 %s', $this->typeLabel($type), $this->formatSize($maxSize)),
            ]);
        }

        // 扩展名校验
        if (! in_array($ext, self::ALLOWED_EXT[$type], true)) {
            Log::warning('[rich-editor] 上传失败：扩展名不允许', [
                'original_name' => $originalName,
                'type' => $type,
                'ext' => $ext,
                'allowed' => self::ALLOWED_EXT[$type],
            ]);

            return response()->json([
                'code' => 1,
                'message' => sprintf('%s 仅支持 %s 格式', $this->typeLabel($type), implode('/', self::ALLOWED_EXT[$type])),
            ]);
        }

        // 保存到 public 磁盘 rich-editor/{type}/{Ymd}/ 目录
        try {
            $filename = 'rich-editor/'.$type.'/'.date('Ymd').'/'.time().'_'.Str::random(8).'.'.$ext;
            Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));

            $url = Storage::disk('public')->url($filename);

            Log::info('[rich-editor] 上传成功', [
                'original_name' => $originalName,
                'type' => $type,
                'saved_path' => $filename,
                'url' => $url,
                'size' => $size,
            ]);

            return response()->json([
                'code' => 0,
                'data' => [
                    'url'  => $url,
                    'path' => $filename,
                    'name' => $filename,
                    'type' => $type,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[rich-editor] 上传异常', [
                'original_name' => $originalName,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['code' => 1, 'message' => '上传失败：'.$e->getMessage()]);
        }
    }

    /**
     * 根据 MIME 和扩展名判断文件类型.
     */
    private function detectType(?string $mime, string $ext): ?string
    {
        if ($mime) {
            if (str_starts_with($mime, 'image/')) {
                return 'image';
            }
            if (str_starts_with($mime, 'video/')) {
                return 'video';
            }
            if (str_starts_with($mime, 'audio/')) {
                return 'audio';
            }
        }

        // MIME 不可靠时按扩展名兜底
        foreach (self::ALLOWED_EXT as $type => $exts) {
            if (in_array($ext, $exts, true)) {
                return $type;
            }
        }

        return null;
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'image' => '图片',
            'video' => '视频',
            'audio' => '音频',
            default => $type,
        };
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1).'MB';
        }

        return round($bytes / 1024, 1).'KB';
    }
}
