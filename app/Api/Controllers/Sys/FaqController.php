<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\FaqCategoriesRequest;
use App\Api\Requests\Sys\FaqQuestionsRequest;
use App\Api\Resources\Sys\FaqCategoriesResource;
use App\Api\Resources\Sys\FaqQuestionsResource;
use App\Api\Services\Sys\FaqService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class FaqController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected FaqService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/sys/faq/categories',
            summary: '常见问题分类列表',
            description: '返回 FAQ 分类列表。',
            tags: ['公共接口/常见问题'],
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
                                    new OA\Property(property: 'id', description: 'FAQ分类ID', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', description: '分类名称', example: '账号问题'),
                                ],
                                type: 'object'
                            )),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function categories(FaqCategoriesRequest $request): JsonResponse
    {
        $result = $this->service->categories();

        return success('success', FaqCategoriesResource::collection($result)->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/sys/faq/questions',
            summary: '常见问题列表',
            description: '按分类查询 FAQ 列表。',
            tags: ['公共接口/常见问题'],
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
                                            new OA\Property(property: 'id', description: 'FAQ条目ID', type: 'integer', example: 1),
                                            new OA\Property(property: 'category_id', type: 'integer', description: '所属分类ID', example: 1),
                                            new OA\Property(property: 'category_name', type: 'string', description: '所属分类名称', example: '账号问题'),
                                            new OA\Property(property: 'question', type: 'string', description: '问题标题', example: '如何修改密码？'),
                                            new OA\Property(property: 'answer', type: 'string', description: '问题回答内容（HTML 富文本）', example: '回答内容...'),
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
    public function questions(FaqQuestionsRequest $request): JsonResponse
    {
        $paginator = $this->service->questions(
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
            'datas'       => FaqQuestionsResource::collection($paginator->getCollection())->toArray($request),
        ];

        return success('success', $data);
    }
}
