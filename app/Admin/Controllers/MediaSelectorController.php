<?php

namespace App\Admin\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaSelectorController
{
    /**
     * 媒体库文件列表（供 MediaSelector 字段弹窗选择）.
     *
     * 约定响应：{"code":0,"data":[{"path":"相对路径","url":"完整URL","name":"文件名"}]}
     * - path：相对 public 磁盘的路径（如 xxx.jpg / dir/xxx.jpg），存库值，展示时用 full_url 拼接
     * - url：完整访问地址（Storage::url）
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $disk = Storage::disk('public');

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        try {
            $files = collect($disk->allFiles())
                // 只保留图片
                ->filter(function ($path) use ($allowedExt) {
                    return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $allowedExt, true);
                })
                // 按修改时间倒序（最新的排前面）
                ->sortByDesc(function ($path) use ($disk) {
                    return $disk->lastModified($path);
                })
                // 最多返回 500 条，避免超大列表
                ->take(500)
                ->values()
                ->map(function ($path) use ($disk) {
                    return [
                        'path' => $path,
                        'url'  => $disk->url($path),
                        'name' => basename($path),
                    ];
                });

            return response()->json(['code' => 0, 'data' => $files]);
        } catch (\Throwable $e) {
            return response()->json(['code' => 1, 'message' => '媒体列表加载失败']);
        }
    }

    /**
     * 上传图片到媒体库（供 MediaSelector 字段弹窗内直接上传，支持批量/拖拽多文件）.
     *
     * 约定响应：{"code":0,"data":[{"path":"相对路径","url":"完整URL","name":"文件名"}]}
     * - data：成功上传的文件数组（单文件时长度为 1）
     * - path：相对 public 磁盘的文件名，存库值，展示时用 full_url 拼接
     * - url：完整访问地址（Storage::url）
     * 校验：仅图片扩展、≤ 5MB；保存到 public 磁盘 {Ymd} 日期子目录
     *       混合上传时跳过不合规文件，全部不合规则返回错误
     *
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        // 兼容单文件（旧调用 file）与多文件（file[]）两种入参
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

            if ($file->getSize() > 5 * 1024 * 1024) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
            if (! in_array($ext, $allowedExt, true)) {
                continue;
            }

            try {
                $filename = date('Ymd').'/'.time().'_'.Str::random(8).'.'.$ext;
                Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));
                $results[] = [
                    'path' => $filename,
                    'url'  => Storage::disk('public')->url($filename),
                    'name' => $filename,
                ];
            } catch (\Throwable $e) {
                // 单张失败跳过，继续处理其余文件
            }
        }

        if (empty($results)) {
            return response()->json(['code' => 1, 'message' => '没有可上传的图片（仅支持 '.implode('/', $allowedExt).'，单张不超过 5MB）']);
        }

        return response()->json(['code' => 0, 'data' => $results]);
    }
}
