<?php

namespace App\Admin\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * 上传图片到媒体库（供 MediaSelector 字段弹窗内直接上传）.
     *
     * 约定响应：{"code":0,"data":{"path":"相对路径","url":"完整URL","name":"文件名"}}
     * - path：相对 public 磁盘的文件名，存库值，展示时用 full_url 拼接
     * - url：完整访问地址（Storage::url）
     * 校验：必须有 file、仅图片扩展、≤ 5MB；保存到 public 磁盘 {Ymd} 日期子目录（与媒体管理器浏览目录一致）
     *
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        if (! $request->hasFile('file')) {
            return response()->json(['code' => 1, 'message' => '请选择要上传的图片']);
        }

        $file = $request->file('file');

        if ($file->getSize() > 5 * 1024 * 1024) {
            return response()->json(['code' => 1, 'message' => '图片大小不能超过 5MB']);
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        if (! in_array($ext, $allowedExt, true)) {
            return response()->json(['code' => 1, 'message' => '仅支持 '.implode('/', $allowedExt).' 格式图片']);
        }

        try {
            $filename = date('Ymd').'/'.time().'_'.Str::random(8).'.'.$ext;

            Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));

            return response()->json([
                'code' => 0,
                'data' => [
                    'path' => $filename,
                    'url'  => Storage::disk('public')->url($filename),
                    'name' => $filename,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['code' => 1, 'message' => '上传失败']);
        }
    }
}
