<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\AgreementsIndexRequest;
use App\Api\Requests\Sys\AgreementsShowRequest;
use App\Api\Services\Sys\AgreementsService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[
    OA\Info(
        version: '1.0.0',
        title: 'ARC Dcat Admin API',
        description: '后台管理系统的 API 接口文档',
    ),
    OA\Server(
        url: '/api',
        description: '本地环境（自动适配当前域名）',
    ),
    OA\Server(
        url: 'https://test.example.com/api',
        description: '测试环境',
    ),
    OA\Server(
        url: 'https://example.com/api',
        description: '正式环境',
    ),
]
class AgreementsController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected AgreementsService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/sys/agreements/list',
            summary: '协议列表',
            tags: ['公共接口/协议管理'],
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
                                    new OA\Property(property: 'title', type: 'string', example: '用户协议'),
                                ],
                                type: 'object'
                            )),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function index(AgreementsIndexRequest $request): JsonResponse
    {
        $result = $this->service->list();

        return success('success', $result);
    }

    #[
        OA\Post(
            path: '/api/sys/agreements/detail',
            summary: '协议详情',
            tags: ['公共接口/协议管理'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', description: '协议ID'),
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
                                    new OA\Property(property: 'title', type: 'string', example: '用户协议'),
                                    new OA\Property(property: 'content', type: 'string', example: '协议内容...'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function show(AgreementsShowRequest $request): JsonResponse
    {
        $result = $this->service->detail($request->input('id'));

        return success('success', $result);
    }
}
