<?php

namespace App\Api\Controllers\Sys;

use App\Api\Services\Sys\ConfigsService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ConfigsController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected ConfigsService $service,
    ) {}

    /**
     * 系统配置
     */
    #[
        OA\Post(
            path: '/api/sys/configs',
            summary: '系统配置',
            tags: ['公共接口/系统配置'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(
                                property: 'data',
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'project_name', type: 'string', example: '项目名称'),
                                ],
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function index(): JsonResponse
    {
        return success('success', $this->service->list());
    }
}
