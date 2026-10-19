<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\EmailLoginRequest;
use App\Api\Services\Login\EmailService;
use App\Api\Resources\Login\LoginResource;
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
            description: '用邮箱 + 邮箱验证码登录；未注册邮箱自动注册并登录。返回 token + 用户信息。',
            tags: ['登录/邮箱登录'],
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
                                            new OA\Property(property: 'id', description: '用户ID', type: 'integer', example: 1),
                                            new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'user@example.com'),
                                            new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13900000001'),
                                            new OA\Property(property: 'email', type: 'string', description: '邮箱（未绑定为空字符串）', example: 'user@example.com'),
                                            new OA\Property(property: 'nickname', type: 'string', description: '昵称', example: '用户0001'),
                                            new OA\Property(property: 'avatar', type: 'string', description: '头像完整 URL（无头像为空字符串）', example: 'https://cdn.example.com/avatar.png'),
                                            new OA\Property(property: 'signature', type: 'string', description: '个性签名（未设置为空字符串）', example: '这个用户很懒'),
                                            new OA\Property(property: 'gender', type: 'string', example: '男', description: '男/女/未知'),
                                            new OA\Property(property: 'age', type: 'integer', description: '年龄（根据生日计算，未设置生日为 0）', example: 26),
                                            new OA\Property(property: 'birthday', type: 'string', description: '生日 Y-m-d（未设置为空字符串）', example: '1998-06-15'),
                                            new OA\Property(property: 'created_at', type: 'string', description: '注册时间 Y-m-d H:i:s', example: '2026-01-01 12:00:00'),
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

        return success('登录成功', (new LoginResource($result))->toArray($request));
    }
}
