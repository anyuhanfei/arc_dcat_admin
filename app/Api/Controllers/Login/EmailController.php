<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\EmailLoginRequest;
use App\Api\Services\Login\EmailService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class EmailController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected EmailService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/login/email/login',
            summary: '邮箱验证码登录',
            tags: ['登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', description: '邮箱（未注册将自动注册）'),
                        new OA\Property(property: 'code', type: 'string', description: '邮箱验证码（6位数字）'),
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
                                            new OA\Property(property: 'account', type: 'string', example: 'user@example.com'),
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
    public function login(EmailLoginRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $code = $request->input('code');

        $result = $this->service->login($email, $code);

        return success('登录成功', $result);
    }
}
