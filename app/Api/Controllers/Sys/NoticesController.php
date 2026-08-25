<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\NoticesIndexRequest;
use App\Api\Requests\Sys\NoticesShowRequest;
use App\Api\Services\Sys\NoticesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NoticesController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected NoticesService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/sys/notices/list',
            summary: '公告列表',
            tags: ['公共接口/公告'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'page', type: 'integer', description: '页码，默认 1'),
                        new OA\Property(property: 'per_page', type: 'integer', description: '每页条数，默认 15'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'total', type: 'integer', example: 10),
                                    new OA\Property(property: 'count', type: 'integer', example: 10),
                                    new OA\Property(property: 'perPage', type: 'integer', example: 15),
                                    new OA\Property(property: 'currentPage', type: 'integer', example: 1),
                                    new OA\Property(property: 'lastPage', type: 'integer', example: 1),
                                    new OA\Property(property: 'firstItem', type: 'integer', example: 1),
                                    new OA\Property(property: 'lastItem', type: 'integer', example: 10),
                                    new OA\Property(property: 'datas', type: 'array', items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'title', type: 'string', example: '公告标题'),
                                            new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                            new OA\Property(property: 'published_at', type: 'string', example: '2025-01-01 12:00:00'),
                                        ],
                                        type: 'object'
                                    )),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function index(NoticesIndexRequest $request): JsonResponse
    {
        $result = $this->service->index($request->integer('per_page', 15));

        return success('success', $result);
    }

    #[
        OA\Post(
            path: '/api/sys/notices/detail',
            summary: '公告详情',
            tags: ['公共接口/公告'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', description: '公告ID'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: '公告标题'),
                                    new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'published_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'content', type: 'string', example: '富文本内容...'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function show(NoticesShowRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $result = $this->service->show($request->input('id'), $user);

        return success('success', $result);
    }

    #[
        OA\Post(
            path: '/api/sys/notices/latest',
            summary: '最新公告',
            tags: ['公共接口/公告'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功（已登录且无未读公告时 data 为 null）',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: '公告标题'),
                                    new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'published_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'content', type: 'string', example: '富文本内容...'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function latest(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $result = $this->service->latest($user);

        return success('success', $result);
    }
}
