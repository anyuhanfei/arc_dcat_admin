<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\PhoneLoginRequest;
use App\Api\Services\Login\PhoneService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PhoneController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected PhoneService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/login/phone/login',
            summary: '手机号验证码登录',
            tags: ['登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'phone', type: 'string', description: '手机号（未注册将自动注册）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '登录成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'token', type: 'string', example: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                    new OA\Property(property: 'user', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'account', type: 'string', example: '13900000001'),
                                            new OA\Property(property: 'nickname', type: 'string', example: '用户0001'),
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
    public function login(PhoneLoginRequest $request): JsonResponse
    {
        $phone = $request->input('phone');
        $code = $request->input('code');

        $result = $this->service->login($phone, $code);

        return success('登录成功', $result);
    }
}
