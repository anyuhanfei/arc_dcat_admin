<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\FaqCategoriesRequest;
use App\Api\Requests\Sys\FaqQuestionsRequest;
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
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: '账号问题'),
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

        return success('success', $result);
    }

    #[
        OA\Post(
            path: '/api/sys/faq/questions',
            summary: '常见问题列表',
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
                                            new OA\Property(property: 'category_name', type: 'string', example: '账号问题'),
                                            new OA\Property(property: 'question', type: 'string', example: '如何修改密码？'),
                                            new OA\Property(property: 'answer', type: 'string', example: '回答内容...'),
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
        $result = $this->service->questions(
            $request->input('category_id'),
            $request->integer('per_page', 15),
        );

        return success('success', $result);
    }
}
