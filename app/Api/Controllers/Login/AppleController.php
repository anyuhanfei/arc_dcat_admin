<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\AppleLoginRequest;
use App\Api\Requests\Login\BindPhoneRequest;
use App\Api\Services\Login\AppleService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AppleController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected AppleService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/login/apple/login',
            summary: 'Apple 登录',
            tags: ['登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'identity_token', type: 'string', description: 'Apple 返回的身份令牌（未注册将自动注册）'),
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
                                            new OA\Property(property: 'account', type: 'string', example: 'apple_001'),
                                            new OA\Property(property: 'nickname', type: 'string', example: '苹果用户0001'),
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
    public function login(AppleLoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->input('identity_token'));

        return success('登录成功', $result);
    }

    #[
        OA\Post(
            path: '/api/login/apple/bind-phone',
            summary: 'Apple 绑定手机号',
            tags: ['登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'phone', type: 'string', description: '手机号'),
                        new OA\Property(property: 'code', type: 'string', description: '短信验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '绑定成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'token', type: 'string', example: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                    new OA\Property(property: 'user', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'phone', type: 'string', example: '13800138000'),
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
    public function bindPhone(BindPhoneRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->bindPhone($user, $request->input('phone'), $request->input('code'));

        return success('绑定成功', $result);
    }
}
