<?php

namespace App\Api\Controllers\Ms;

use App\Api\Requests\Ms\EmailSendRequest;
use App\Api\Services\Ms\EmailService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class EmailController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected EmailService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/email/send',
            summary: '发送邮箱验证码',
            tags: ['公共接口'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', description: '邮箱（可选，不传则使用登录账号邮箱）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '验证码已发送'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function send(EmailSendRequest $request): JsonResponse
    {
        $email = $request->input('email');
        // 公开路由无 auth:sanctum 中间件，用 auth('sanctum')->user() 主动解析 Bearer token（可选登录）
        $user = auth('sanctum')->user();

        $result = $this->service->send($email, $user);

        return success('验证码已发送', $result);
    }
}
