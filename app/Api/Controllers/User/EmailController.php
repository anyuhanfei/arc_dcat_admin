<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\EmailChangeRequest;
use App\Api\Requests\User\EmailUpdateNewRequest;
use App\Api\Requests\User\EmailUpdateRequest;
use App\Api\Requests\User\EmailVerifyOldRequest;
use App\Api\Services\User\EmailService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class EmailController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected EmailService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/user/email/update',
            summary: '修改邮箱（一步验证版）',
            tags: ['用户/修改邮箱信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'old_email', type: 'string', description: '旧邮箱'),
                        new OA\Property(property: 'old_code', type: 'string', description: '旧邮箱验证码（6位数字）'),
                        new OA\Property(property: 'new_email', type: 'string', description: '新邮箱'),
                        new OA\Property(property: 'new_code', type: 'string', description: '新邮箱验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '邮箱修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function update(EmailUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $userInfo = $this->service->update($user, $request->input('old_email'), $request->input('old_code'), $request->input('new_email'), $request->input('new_code'));

        return success('邮箱修改成功', $userInfo);
    }

    #[
        OA\Post(
            path: '/api/user/email/verify-old',
            summary: '验证旧邮箱（两步验证版）',
            tags: ['用户/修改邮箱信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'old_email', type: 'string', description: '旧邮箱'),
                        new OA\Property(property: 'old_code', type: 'string', description: '旧邮箱验证码（6位数字）'),
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
    public function verifyOld(EmailVerifyOldRequest $request): JsonResponse
    {
        $user = $request->user();

        $token = $this->service->verifyOld($user, $request->input('old_email'), $request->input('old_code'));

        return success('验证成功', ['verify_token' => $token]);
    }

    #[
        OA\Post(
            path: '/api/user/email/change',
            summary: '提交新邮箱（两步验证版）',
            tags: ['用户/修改邮箱信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'verify_token', type: 'string', description: '第一步签发的验证标识'),
                        new OA\Property(property: 'new_email', type: 'string', description: '新邮箱'),
                        new OA\Property(property: 'new_code', type: 'string', description: '新邮箱验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '邮箱修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function change(EmailChangeRequest $request): JsonResponse
    {
        $user = $request->user();

        $userInfo = $this->service->change($user, $request->input('verify_token'), $request->input('new_email'), $request->input('new_code'));

        return success('邮箱修改成功', $userInfo);
    }

    #[
        OA\Post(
            path: '/api/user/email/update-new',
            summary: '修改邮箱（不验证旧邮箱版）',
            tags: ['用户/修改邮箱信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'new_email', type: 'string', description: '新邮箱'),
                        new OA\Property(property: 'new_code', type: 'string', description: '新邮箱验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '邮箱修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function updateNew(EmailUpdateNewRequest $request): JsonResponse
    {
        $user = $request->user();

        $userInfo = $this->service->updateNew($user, $request->input('new_email'), $request->input('new_code'));

        return success('邮箱修改成功', $userInfo);
    }
}
