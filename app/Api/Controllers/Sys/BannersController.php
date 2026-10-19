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
            description: '按位置标识返回首页/指定位置的轮播图列表，按排序字段正序返回，用于 APP 首页 banner 展示。',
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
                            new OA\Property(property: 'data', type: 'array', description: '轮播图列表', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', description: '轮播图ID', type: 'integer', example: 1),
                                    new OA\Property(property: 'position', type: 'string', description: '位置标识', example: 'home'),
                                    new OA\Property(property: 'position_name', type: 'string', description: '位置中文名称', example: '首页轮播'),
                                    new OA\Property(property: 'image', type: 'string', description: '轮播图完整 URL', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'link_type', type: 'string', description: '跳转类型：none=无跳转，其他取值见 link_type_name', example: 'none'),
                                    new OA\Property(property: 'link_type_name', type: 'string', description: '跳转类型中文名称', example: '无关联'),
                                    new OA\Property(property: 'link_value', type: 'string', description: '跳转目标值（如链接 URL、文章 ID 等，无跳转时为空字符串）', example: ''),
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
