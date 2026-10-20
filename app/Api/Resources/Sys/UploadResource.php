<?php

namespace App\Api\Resources\Sys;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 上传结果输出（resource 为 UploadService::handle 返回的数组）
 */
class UploadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'path'       => $this->resource['path'] ?? '',
            'url'        => $this->resource['url'] ?? '',
            'type'       => $this->resource['type'] ?? '',
            'ext'        => $this->resource['ext'] ?? '',
            'size'       => (int) ($this->resource['size'] ?? 0),
            // 仅视频返回封面
            'cover_path' => $this->resource['cover_path'] ?? '',
            'cover_url'  => $this->resource['cover_url'] ?? '',
        ];
    }
}
