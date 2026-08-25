<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\ArticleCategoriesRequest;
use App\Api\Requests\Sys\ArticlesIndexRequest;
use App\Api\Requests\Sys\ArticlesShowRequest;
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
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: '平台公告'),
                                    new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
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

        return success('success', $result);
    }

    #[
        OA\Post(
            path: '/api/sys/articles/list',
            summary: '文章列表',
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
                                            new OA\Property(property: 'category_id', type: 'integer', example: 1),
                                            new OA\Property(property: 'category_name', type: 'string', example: '平台公告'),
                                            new OA\Property(property: 'title', type: 'string', example: '文章标题'),
                                            new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                            new OA\Property(property: 'author', type: 'string', example: '管理员'),
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
    public function index(ArticlesIndexRequest $request): JsonResponse
    {
        $result = $this->service->index(
            $request->input('category_id'),
            $request->integer('per_page', 15),
        );

        return success('success', $result);
    }

    #[
        OA\Post(
            path: '/api/sys/articles/detail',
            summary: '文章详情',
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
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'category_name', type: 'string', example: '平台公告'),
                                    new OA\Property(property: 'title', type: 'string', example: '文章标题'),
                                    new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'author', type: 'string', example: '管理员'),
                                    new OA\Property(property: 'published_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'summary', type: 'string', example: '文章摘要'),
                                    new OA\Property(property: 'keywords', type: 'array', items: new OA\Items(type: 'string')),
                                    new OA\Property(property: 'content', type: 'string', example: '富文本内容...'),
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

        return success('success', $result);
    }
}
