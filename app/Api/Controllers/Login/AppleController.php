<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\AppleLoginRequest;
use App\Api\Requests\Login\BindPhoneRequest;
use App\Api\Services\Login\AppleService;
use App\Api\Resources\Login\LoginResource;
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
            description: 'Apple Sign In 授权登录；首次登录自动注册。返回 token + 用户信息。',
            tags: ['登录/Apple 登录'],
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
                                            new OA\Property(property: 'id', description: '用户ID', type: 'integer', example: 1),
                                            new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'apple_001'),
                                            new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13900000001'),
                                            new OA\Property(property: 'email', type: 'string', description: '邮箱（未绑定为空字符串）', example: 'user@example.com'),
                                            new OA\Property(property: 'nickname', type: 'string', description: '昵称', example: '苹果用户0001'),
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
    public function login(AppleLoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->input('identity_token'));

        return success('登录成功', (new LoginResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/login/apple/bind-phone',
            summary: 'Apple 绑定手机号',
            description: 'Apple 登录后绑定手机号；返回新 token + 用户信息。',
            tags: ['登录/Apple 登录'],
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
                                            new OA\Property(property: 'id', description: '用户ID', type: 'integer', example: 1),
                                            new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'apple_001'),
                                            new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13800138000'),
                                            new OA\Property(property: 'email', type: 'string', description: '邮箱（未绑定为空字符串）', example: 'user@example.com'),
                                            new OA\Property(property: 'nickname', type: 'string', description: '昵称', example: '苹果用户0001'),
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
    public function bindPhone(BindPhoneRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->bindPhone($user, $request->input('phone'), $request->input('code'));

        return success('绑定成功', (new LoginResource($result))->toArray($request));
    }
}
