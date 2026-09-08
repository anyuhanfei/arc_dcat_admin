<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\YidunLoginRequest;
use App\Api\Services\Login\YidunService;
use App\Api\Resources\Login\LoginResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class YidunController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected YidunService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/login/yidun/login',
            summary: '网易易盾一键登录',
            tags: ['登录/手机号登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', description: '易盾客户端 SDK 返回的 token'),
                        new OA\Property(property: 'access_token', type: 'string', description: '易盾客户端 SDK 返回的 accessToken'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '登录成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'token', type: 'string', example: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                    new OA\Property(property: 'user', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'account', type: 'string', example: '13900000002'),
                                            new OA\Property(property: 'nickname', type: 'string', example: '用户0002'),
                                        ]
                                    ),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function login(YidunLoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->input('token'), $request->input('access_token'));

        return success('登录成功', (new LoginResource($result))->toArray($request));
    }
}
