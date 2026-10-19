<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\ArticleCategoriesRequest;
use App\Api\Requests\Sys\ArticlesIndexRequest;
use App\Api\Requests\Sys\ArticlesShowRequest;
use App\Api\Resources\Sys\ArticleCategoriesResource;
use App\Api\Resources\Sys\ArticlesResource;
use App\Api\Services\Sys\ArticlesService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ArticlesController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected ArticlesService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/sys/articles/categories',
            summary: '文章分类列表',
            description: '返回资讯/帮助中心的文章分类树。',
            tags: ['公共接口/文章'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', description: '文章ID', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: '平台公告'),
                                    new OA\Property(property: 'image', type: 'string', description: '封面图完整 URL', example: 'https://example.com/uploads/xxx.jpg'),
                                ],
                                type: 'object'
                            )),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function categories(ArticleCategoriesRequest $request): JsonResponse
    {
        $result = $this->service->categories();

        return success('success', ArticleCategoriesResource::collection($result)->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/sys/articles/list',
            summary: '文章列表',
            description: '按分类分页查询文章列表。',
            tags: ['公共接口/文章'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'category_id', type: 'integer', description: '分类ID（可选，不传则返回全部）'),
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
                                            new OA\Property(property: 'id', description: '文章ID', type: 'integer', example: 1),
                                            new OA\Property(property: 'category_id', type: 'integer', description: '所属分类ID', example: 1),
                                            new OA\Property(property: 'category_name', type: 'string', description: '所属分类名称', example: '平台公告'),
                                            new OA\Property(property: 'title', type: 'string', description: '文章标题', example: '文章标题'),
                                            new OA\Property(property: 'image', type: 'string', description: '封面图完整 URL', example: 'https://example.com/uploads/xxx.jpg'),
                                            new OA\Property(property: 'author', type: 'string', description: '作者', example: '管理员'),
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
    public function index(ArticlesIndexRequest $request): JsonResponse
    {
        $paginator = $this->service->index(
            $request->input('category_id'),
            $request->integer('per_page', 15),
        );

        $data = [
            'total'       => $paginator->total(),
            'count'       => $paginator->count(),
            'perPage'     => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage'    => $paginator->lastPage(),
            'firstItem'   => $paginator->firstItem(),
            'lastItem'    => $paginator->lastItem(),
            'datas'       => ArticlesResource::collection($paginator->getCollection())->toArray($request),
        ];

        return success('success', $data);
    }

    #[
        OA\Post(
            path: '/api/sys/articles/detail',
            summary: '文章详情',
            description: '按 ID 查询单篇文章完整内容（含 HTML 正文）。',
            tags: ['公共接口/文章'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', description: '文章ID'),
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
                                    new OA\Property(property: 'id', description: '文章ID', type: 'integer', example: 1),
                                    new OA\Property(property: 'category_id', type: 'integer', description: '所属分类ID', example: 1),
                                    new OA\Property(property: 'category_name', type: 'string', description: '所属分类名称', example: '平台公告'),
                                    new OA\Property(property: 'title', type: 'string', description: '文章标题', example: '文章标题'),
                                    new OA\Property(property: 'image', type: 'string', description: '封面图完整 URL', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'author', type: 'string', description: '作者', example: '管理员'),
                                    new OA\Property(property: 'published_at', type: 'string', description: '发布时间 Y-m-d H:i:s', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'summary', type: 'string', description: '文章摘要（列表展示用）', example: '文章摘要'),
                                    new OA\Property(property: 'keywords', type: 'array', description: '文章标签关键词', items: new OA\Items(type: 'string')),
                                    new OA\Property(property: 'content', type: 'string', description: '文章正文（HTML 富文本）', example: '富文本内容...'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function show(ArticlesShowRequest $request): JsonResponse
    {
        $result = $this->service->show($request->input('id'));

        return success('success', (new ArticlesResource($result))->toArray($request));
    }
}
