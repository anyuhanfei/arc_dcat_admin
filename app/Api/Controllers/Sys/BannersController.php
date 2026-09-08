<?php

namespace App\Api\Controllers\Sys;

use App\Api\Requests\Sys\BannersListRequest;
use App\Api\Resources\Sys\BannersResource;
use App\Api\Services\Sys\BannersService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class BannersController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected BannersService $service,
    ) {}

    /**
     * 轮播图列表
     *
     * 注意：实际项目中确定轮播图所有位置后，需更新此方法的接口文档。
     */
    #[
        OA\Post(
            path: '/api/sys/banners/list',
            summary: '轮播图列表',
            tags: ['公共接口/轮播图'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'position', type: 'string', description: '位置标识（当前已配置：home，实际项目确定全部位置后更新本文档；当仅配置一个位置时可省略）'),
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
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'position', type: 'string', example: 'home'),
                                    new OA\Property(property: 'position_name', type: 'string', example: '首页轮播'),
                                    new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'link_type', type: 'string', example: 'none'),
                                    new OA\Property(property: 'link_type_name', type: 'string', example: '无关联'),
                                    new OA\Property(property: 'link_value', type: 'string', example: ''),
                                ],
                                type: 'object'
                            )),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function index(BannersListRequest $request): JsonResponse
    {
        $result = $this->service->list($request->input('position'));

        return success('success', BannersResource::collection($result)->toArray($request));
    }
}
