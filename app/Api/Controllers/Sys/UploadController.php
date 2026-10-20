<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\UploadRequest;
use App\Api\Resources\Sys\UploadResource;
use App\Api\Services\Sys\UploadService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UploadController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected UploadService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/sys/upload',
            summary: '上传文件',
            description: '登录态上传接口，接收单个文件（multipart/form-data，字段名 file）。'
                . '允许图片(jpg/jpeg/png/gif/webp)、视频(mp4/mov)、常见文档(pdf/doc/docx/xls/xlsx/ppt/zip)，'
                . '单文件大小上限由 UPLOAD_MAX_SIZE_MB 配置（默认100MB）。'
                . '返回相对路径 path 与带域名完整 URL；视频额外截取第1秒帧作为同名 jpg 封面，返回 cover_path/cover_url。'
                . '开启云存储(CLOUD_DRIVER=cos/aliyun)时文件直接上传到云，完整链接走云自定义域名。',
            tags: ['公共接口'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'file',
                                type: 'string',
                                format: 'binary',
                                description: '待上传文件（图片/视频/文档）'
                            ),
                        ],
                        required: ['file']
                    )
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: '上传成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'path', description: '文件相对路径（不含域名，数据库存储用）', type: 'string', example: '20261020/20261020_120000_abcd1234.jpg'),
                                    new OA\Property(property: 'url', description: '带域名的完整文件 URL', type: 'string', example: 'https://cdn.example.com/20261020/xxx.jpg'),
                                    new OA\Property(property: 'type', description: '文件类型：image=图片，video=视频，doc=文档', type: 'string', example: 'image'),
                                    new OA\Property(property: 'ext', description: '小写扩展名（不带点）', type: 'string', example: 'jpg'),
                                    new OA\Property(property: 'size', description: '文件大小（字节）', type: 'integer', example: 102400),
                                    new OA\Property(property: 'cover_path', description: '视频封面相对路径（仅视频返回，非视频为空）', type: 'string', example: '20261020/xxx.jpg'),
                                    new OA\Property(property: 'cover_url', description: '视频封面完整 URL（仅视频返回，非视频为空）', type: 'string', example: 'https://cdn.example.com/20261020/xxx.jpg'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function store(UploadRequest $request): JsonResponse
    {
        $result = $this->service->handle($request->file('file'));

        return success('上传成功', (new UploadResource($result))->toArray($request));
    }
}
