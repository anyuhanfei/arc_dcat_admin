<?php

namespace App\Api\Controllers\Ms;

use App\Api\Requests\Ms\SmsSendRequest;
use App\Api\Resources\Ms\SmsResource;
use App\Api\Services\Ms\SmsService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SmsController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected SmsService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/sms/send',
            summary: '发送短信验证码',
            tags: ['公共接口'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'phone', type: 'string', description: '手机号（可选，不传则使用登录账号手机号）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '验证码发送成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'phone', type: 'string', example: '13800138000'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function send(SmsSendRequest $request): JsonResponse
    {
        $phone = $request->input('phone');
        // 公开路由无 auth:sanctum 中间件，$request->user() 恒为 null；
        // 用 auth('sanctum')->user() 主动解析 Bearer token：有有效 token 返回用户，无/无效返回 null（可选登录）
        $user = auth('sanctum')->user();

        $result = $this->service->send($phone, $user);

        return success('验证码发送成功', (new SmsResource($result))->toArray($request));
    }
}
