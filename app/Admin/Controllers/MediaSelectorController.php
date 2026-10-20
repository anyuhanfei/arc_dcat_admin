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
     * 缩略图统一存本地 public disk（thumb 前缀），列表以本地缩略图为数据源；
     * 选中后由 thumb 名还原原图相对键，云模式再拼云原图链接，表单存相对键.
     *
     * 约定响应：{"code":0,"data":{"list":[{"path":"...","url":"...","thumb_url":"...","name":"..."}],"total":100,"page":1}}
     * - path：原图相对键（存库值）
     * - url：原图完整 URL（云模式走云域名，本地走 public disk）
     * - thumb_url：本地缩略图完整 URL
     */
    public function list(Request $request): JsonResponse
    {
        $disk = Storage::disk('public');
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        $page = max(1, (int) $request->get('page', 1));
        $pageSize = min(100, max(12, (int) $request->get('page_size', 48)));

        try {
            // 已软删除的原图路径，列表中过滤掉
            $deletedPaths = DB::table('media_deleted_files')->pluck('path')->all();

            $isCloud = \App\Integrations\Storage\StorageManager::isCloud();
            $drive = $isCloud ? \App\Integrations\Storage\StorageManager::drive() : null;

            // 数据源：本地 public disk 中的 thumb 缩略图文件
            $thumbs = collect($disk->allFiles())
                ->filter(function ($thumbPath) use ($allowedExt, $deletedPaths) {
                    $basename = basename($thumbPath);
                    if (! Str::startsWith($basename, 'thumb')) {
                        return false;
                    }
                    $ext = strtolower(pathinfo($thumbPath, PATHINFO_EXTENSION));
                    if (! in_array($ext, $allowedExt, true)) {
                        return false;
                    }
                    // 按还原出的原图键过滤软删除
                    $originalKey = $this->originalKeyFromThumb($thumbPath);
                    if (in_array($originalKey, $deletedPaths, true)) {
                        return false;
                    }
                    return true;
                })
                ->sortByDesc(fn ($thumbPath) => $disk->lastModified($thumbPath))
                ->values();

            $total = $thumbs->count();
            $pageItems = $thumbs->forPage($page, $pageSize)->map(function ($thumbPath) use ($disk, $drive, $isCloud) {
                $originalKey = $this->originalKeyFromThumb($thumbPath);
                $originalUrl = $isCloud ? $drive->url($originalKey) : $disk->url($originalKey);

                return [
                    'path'      => $originalKey,
                    'url'       => $originalUrl,
                    'thumb_url' => $disk->url($thumbPath),
                    'name'      => basename($originalKey),
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
     * 由 thumb 缩略图相对路径还原原图相对键（去掉文件名前缀 "thumb"）.
     */
    private function originalKeyFromThumb(string $thumbPath): string
    {
        $info = pathinfo($thumbPath);
        $dir = $info['dirname'] === '.' ? '' : $info['dirname'].'/';

        return $dir.Str::substr($info['basename'], 5);
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

                // 原图：云模式传云驱动，本地写 public disk
                if (\App\Integrations\Storage\StorageManager::isCloud()) {
                    $drive = \App\Integrations\Storage\StorageManager::drive();
                    $drive->putFile($file->getRealPath(), $filename);
                    $originalUrl = $drive->url($filename);
                } else {
                    Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));
                    $originalUrl = Storage::disk('public')->url($filename);
                }

                // 缩略图统一存本地 public disk（不传云）
                $thumbFilename = $this->generateThumb($file, $filename);

                $results[] = [
                    'path'      => $filename,
                    'url'       => $originalUrl,
                    'thumb_url' => $thumbFilename ? Storage::disk('public')->url($thumbFilename) : $originalUrl,
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
            $encoded = (string) $image->encode(null, 80);

            // 缩略图统一存本地 public disk，不传云
            Storage::disk('public')->put($thumbPath, $encoded);

            return $thumbPath;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
