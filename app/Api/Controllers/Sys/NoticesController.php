<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\NoticesIndexRequest;
use App\Api\Requests\Sys\NoticesShowRequest;
use App\Api\Resources\Sys\NoticesResource;
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
            description: '分页查询平台公告列表，按发布时间倒序。',
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
                                    new OA\Property(property: 'total', type: 'integer', description: '总条数', example: 10),
                                    new OA\Property(property: 'count', type: 'integer', description: '当前页条数', example: 10),
                                    new OA\Property(property: 'perPage', type: 'integer', description: '每页条数', example: 15),
                                    new OA\Property(property: 'currentPage', type: 'integer', description: '当前页码', example: 1),
                                    new OA\Property(property: 'lastPage', type: 'integer', description: '最后一页页码', example: 1),
                                    new OA\Property(property: 'firstItem', type: 'integer', description: '当前页第一条序号', example: 1),
                                    new OA\Property(property: 'lastItem', type: 'integer', description: '当前页最后一条序号', example: 10),
                                    new OA\Property(property: 'datas', type: 'array', items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: '公告ID', type: 'integer', example: 1),
                                            new OA\Property(property: 'title', type: 'string', description: '公告标题', example: '公告标题'),
                                            new OA\Property(property: 'image', type: 'string', description: '公告封面图完整 URL', example: 'https://example.com/uploads/xxx.jpg'),
                                            new OA\Property(property: 'published_at', type: 'string', description: '发布时间 Y-m-d H:i:s', example: '2025-01-01 12:00:00'),
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
        $paginator = $this->service->index($request->integer('per_page', 15));

        $data = [
            'total'       => $paginator->total(),
            'count'       => $paginator->count(),
            'perPage'     => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage'    => $paginator->lastPage(),
            'firstItem'   => $paginator->firstItem(),
            'lastItem'    => $paginator->lastItem(),
            'datas'       => NoticesResource::collection($paginator->getCollection())->toArray($request),
        ];

        return success('success', $data);
    }

    #[
        OA\Post(
            path: '/api/sys/notices/detail',
            summary: '公告详情',
            description: '按 ID 查询单条公告完整内容。',
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
                                    new OA\Property(property: 'id', description: '公告ID', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', description: '公告标题', example: '公告标题'),
                                    new OA\Property(property: 'image', type: 'string', description: '公告封面图完整 URL', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'published_at', type: 'string', description: '发布时间 Y-m-d H:i:s', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'content', type: 'string', description: '公告正文（HTML 富文本）', example: '富文本内容...'),
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

        return success('success', (new NoticesResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/sys/notices/latest',
            summary: '最新公告',
            description: '获取最新一条公告（APP 启动/首页弹窗用）。',
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
                                    new OA\Property(property: 'id', description: '公告ID', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', description: '公告标题', example: '公告标题'),
                                    new OA\Property(property: 'image', type: 'string', description: '公告封面图完整 URL', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'published_at', type: 'string', description: '发布时间 Y-m-d H:i:s', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'content', type: 'string', description: '公告正文（HTML 富文本）', example: '富文本内容...'),
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

        if ($result === null) {
            return success('success', null);
        }

        return success('success', (new NoticesResource($result))->toArray($request));
    }
}
