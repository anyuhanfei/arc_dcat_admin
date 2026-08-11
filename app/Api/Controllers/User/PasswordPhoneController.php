<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\PasswordPhoneChangeRequest;
use App\Api\Requests\User\PasswordPhoneUpdateRequest;
use App\Api\Requests\User\PasswordPhoneVerifyRequest;
use App\Api\Services\User\PasswordPhoneService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PasswordPhoneController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected PasswordPhoneService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/user/password/phone/update',
            summary: '手机号验证码改密（一步验证版）',
            tags: ['用户/手机号改密'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'phone', type: 'string', description: '手机号（可选，不传使用登录账号手机号）'),
                        new OA\Property(property: 'code', type: 'string', description: '短信验证码（6位数字）'),
                        new OA\Property(property: 'password', type: 'string', description: '新密码（至少6位）'),
                        new OA\Property(property: 'password_confirmation', type: 'string', description: '确认新密码'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '密码修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function update(PasswordPhoneUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->service->update($user, $request->input('phone'), $request->input('code'), $request->input('password'));

        return success('密码修改成功');
    }

    #[
        OA\Post(
            path: '/api/user/password/phone/verify',
            summary: '验证手机号（两步验证版）',
            tags: ['用户/手机号改密'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'phone', type: 'string', description: '手机号（可选，不传使用登录账号手机号）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '验证成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'verify_token', type: 'string', example: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function verify(PasswordPhoneVerifyRequest $request): JsonResponse
    {
        $user = $request->user();

        $token = $this->service->verify($user, $request->input('phone'), $request->input('code'));

        return success('验证成功', ['verify_token' => $token]);
    }

    #[
        OA\Post(
            path: '/api/user/password/phone/change',
            summary: '改密（两步验证版）',
            tags: ['用户/手机号改密'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'verify_token', type: 'string', description: '第一步签发的验证标识'),
                        new OA\Property(property: 'password', type: 'string', description: '新密码（至少6位）'),
                        new OA\Property(property: 'password_confirmation', type: 'string', description: '确认新密码'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '密码修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function change(PasswordPhoneChangeRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->service->change($user, $request->input('verify_token'), $request->input('password'));

        return success('密码修改成功');
    }
}
