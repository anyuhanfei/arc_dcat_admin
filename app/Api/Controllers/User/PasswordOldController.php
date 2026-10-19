<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\PasswordOldChangeRequest;
use App\Api\Requests\User\PasswordOldUpdateRequest;
use App\Api\Requests\User\PasswordOldVerifyRequest;
use App\Api\Services\User\PasswordOldService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PasswordOldController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected PasswordOldService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/user/password/old/update',
            summary: '旧密码改密（一步验证版）',
            description: '通过旧密码直接修改新密码（一步完成）。',
            tags: ['用户/旧密码改密'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'old_password', type: 'string', description: '旧密码'),
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
    public function update(PasswordOldUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->service->update($user, $request->input('old_password'), $request->input('password'));

        return success('密码修改成功');
    }

    #[
        OA\Post(
            path: '/api/user/password/old/verify',
            summary: '验证旧密码（两步验证版）',
            description: '两步改密第一步：校验旧密码后下发 verify_token。',
            tags: ['用户/旧密码改密'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'old_password', type: 'string', description: '旧密码'),
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
    public function verify(PasswordOldVerifyRequest $request): JsonResponse
    {
        $user = $request->user();

        $token = $this->service->verify($user, $request->input('old_password'));

        return success('验证成功', ['verify_token' => $token]);
    }

    #[
        OA\Post(
            path: '/api/user/password/old/change',
            summary: '改密（两步验证版）',
            description: '两步改密第二步：用 verify_token + 新密码完成改密。',
            tags: ['用户/旧密码改密'],
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
    public function change(PasswordOldChangeRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->service->change($user, $request->input('verify_token'), $request->input('password'));

        return success('密码修改成功');
    }
}
