<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\PasswordEmailChangeRequest;
use App\Api\Requests\User\PasswordEmailUpdateRequest;
use App\Api\Requests\User\PasswordEmailVerifyRequest;
use App\Api\Services\User\PasswordEmailService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PasswordEmailController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected PasswordEmailService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/user/password/email/update',
            summary: '邮箱验证码改密（一步验证版）',
            description: '通过邮箱验证码直接重置密码（一步完成）。',
            tags: ['用户/邮箱改密'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', description: '邮箱（可选，不传使用登录账号邮箱）'),
                        new OA\Property(property: 'code', type: 'string', description: '邮箱验证码（6位数字）'),
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
    public function update(PasswordEmailUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->service->update($user, $request->input('email'), $request->input('code'), $request->input('password'));

        return success('密码修改成功');
    }

    #[
        OA\Post(
            path: '/api/user/password/email/verify',
            summary: '验证邮箱验证码（两步验证版）',
            description: '两步改密第一步：校验邮箱验证码后下发 verify_token。',
            tags: ['用户/邮箱改密'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', description: '邮箱（可选，不传使用登录账号邮箱）'),
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
    public function verify(PasswordEmailVerifyRequest $request): JsonResponse
    {
        $user = $request->user();

        $token = $this->service->verify($user, $request->input('email'), $request->input('code'));

        return success('验证成功', ['verify_token' => $token]);
    }

    #[
        OA\Post(
            path: '/api/user/password/email/change',
            summary: '改密（两步验证版）',
            description: '两步改密第二步：用 verify_token + 新密码完成改密。',
            tags: ['用户/邮箱改密'],
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
    public function change(PasswordEmailChangeRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->service->change($user, $request->input('verify_token'), $request->input('password'));

        return success('密码修改成功');
    }
}
