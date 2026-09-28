<?php

namespace App\Admin\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as InterventionImage;

class MediaSelectorController
{
    /**
     * 媒体库文件列表（分页）.
     *
     * 约定响应：{"code":0,"data":{"list":[{"path":"...","url":"...","thumb_url":"...","name":"..."}],"total":100,"page":1}}
     * - path：相对 public 磁盘的原图路径（存库值）
     * - url：原图完整 URL
     * - thumb_url：缩略图完整 URL（文件名 thumb 前缀），无缩略图时回退到原图 URL
     *
     * 过滤规则：跳过 thumb 开头的文件（这些是缩略图，不作为独立媒体项展示）
     */
    public function list(Request $request): JsonResponse
    {
        $disk = Storage::disk('public');
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        $page = max(1, (int) $request->get('page', 1));
        $pageSize = min(100, max(12, (int) $request->get('page_size', 48)));

        try {
            // 已软删除的文件路径，列表中过滤掉
            $deletedPaths = DB::table('media_deleted_files')->pluck('path')->all();

            $allFiles = collect($disk->allFiles())
                ->filter(function ($path) use ($allowedExt, $deletedPaths) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (! in_array($ext, $allowedExt, true)) {
                        return false;
                    }
                    // 跳过缩略图文件（thumb 前缀），它们不是独立媒体项
                    $basename = basename($path);
                    if (Str::startsWith($basename, 'thumb')) {
                        return false;
                    }
                    // 跳过已软删除的文件
                    if (in_array($path, $deletedPaths, true)) {
                        return false;
                    }
                    return true;
                })
                ->sortByDesc(function ($path) use ($disk) {
                    return $disk->lastModified($path);
                })
                ->values();

            $total = $allFiles->count();
            $pageItems = $allFiles->forPage($page, $pageSize)->map(function ($path) use ($disk) {
                // 构造缩略图路径：同目录下文件名前加 thumb
                $info = pathinfo($path);
                $thumbPath = ($info['dirname'] === '.' ? '' : $info['dirname'].'/').'thumb'.$info['basename'];

                return [
                    'path'      => $path,
                    'url'       => $disk->url($path),
                    // 有缩略图用缩略图，没有则回退原图
                    'thumb_url' => $disk->exists($thumbPath) ? $disk->url($thumbPath) : $disk->url($path),
                    'name'      => basename($path),
                ];
            });

            return response()->json([
                'code' => 0,
                'data' => [
                    'list'  => $pageItems->values(),
                    'total' => $total,
                    'page'  => $page,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['code' => 1, 'message' => '媒体列表加载失败: '.$e->getMessage()]);
        }
    }

    /**
     * 上传图片到媒体库（弹窗内直接上传，支持批量/拖拽多文件）.
     *
     * 上传后自动生成 thumb 前缀缩略图，与 Dcat 上传字段保持一致.
     */
    public function upload(Request $request): JsonResponse
    {
        $files = $request->file('file') ?? $request->file('file[]');
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (! is_array($files) || empty($files)) {
            return response()->json(['code' => 1, 'message' => '请选择要上传的图片']);
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $results = [];

        foreach ($files as $file) {
            if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
                continue;
            }

            if ($file->getSize() > 10 * 1024 * 1024) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
            if (! in_array($ext, $allowedExt, true)) {
                continue;
            }

            try {
                $filename = date('Ymd').'/'.date('Ymd_His').'_'.Str::random(8).'.'.$ext;
                Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));

                // 生成 thumb 缩略图
                $thumbFilename = $this->generateThumb($file, $filename);

                $results[] = [
                    'path'      => $filename,
                    'url'       => Storage::disk('public')->url($filename),
                    'thumb_url' => $thumbFilename ? Storage::disk('public')->url($thumbFilename) : Storage::disk('public')->url($filename),
                    'name'      => basename($filename),
                ];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('媒体库上传失败: '.$e->getMessage());
            }
        }

        if (empty($results)) {
            return response()->json(['code' => 1, 'message' => '没有可上传的图片（仅支持 '.implode('/', $allowedExt).'，单张不超过 10MB）']);
        }

        return response()->json(['code' => 0, 'data' => $results]);
    }

    /**
     * 软删除媒体库文件：不删磁盘文件，只记录删除标记，列表中不再显示.
     */
    public function delete(Request $request): JsonResponse
    {
        $path = $request->get('path');
        if (! $path || ! is_string($path)) {
            return response()->json(['code' => 1, 'message' => '缺少文件路径']);
        }

        $path = ltrim(str_replace('..', '', $path), '/');

        try {
            DB::table('media_deleted_files')->updateOrInsert(
                ['path' => $path],
                ['created_at' => now(), 'updated_at' => now()]
            );

            return response()->json(['code' => 0, 'message' => '已删除']);
        } catch (\Throwable $e) {
            return response()->json(['code' => 1, 'message' => '删除失败: '.$e->getMessage()]);
        }
    }

    /**
     * 生成 thumb 前缀缩略图.
     */
    protected function generateThumb(UploadedFile $file, string $originalPath): ?string
    {
        try {
            $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
            $image = $manager->make($file->getRealPath());

            if ($image->width() > 400) {
                $image->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $info = pathinfo($originalPath);
            $thumbPath = ($info['dirname'] === '.' ? '' : $info['dirname'].'/').'thumb'.$info['basename'];

            Storage::disk('public')->put($thumbPath, (string) $image->encode(null, 80));

            return $thumbPath;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
