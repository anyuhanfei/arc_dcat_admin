<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\PhoneChangeRequest;
use App\Api\Requests\User\PhoneUpdateNewRequest;
use App\Api\Requests\User\PhoneUpdateRequest;
use App\Api\Requests\User\PhoneVerifyOldRequest;
use App\Api\Resources\User\UserResource;
use App\Api\Services\User\PhoneService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PhoneController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected PhoneService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/user/phone/update',
            summary: '修改手机号（一步验证版）',
            tags: ['用户/修改手机号信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'old_phone', type: 'string', description: '旧手机号'),
                        new OA\Property(property: 'old_code', type: 'string', description: '旧手机号验证码（6位数字）'),
                        new OA\Property(property: 'new_phone', type: 'string', description: '新手机号'),
                        new OA\Property(property: 'new_code', type: 'string', description: '新手机号验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '手机号修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function update(PhoneUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->update($user, $request->input('old_phone'), $request->input('old_code'), $request->input('new_phone'), $request->input('new_code'));

        return success('手机号修改成功', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/user/phone/verify-old',
            summary: '验证旧手机号（两步验证版）',
            tags: ['用户/修改手机号信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'old_phone', type: 'string', description: '旧手机号'),
                        new OA\Property(property: 'old_code', type: 'string', description: '旧手机号验证码（6位数字）'),
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
    public function verifyOld(PhoneVerifyOldRequest $request): JsonResponse
    {
        $user = $request->user();

        $token = $this->service->verifyOld($user, $request->input('old_phone'), $request->input('old_code'));

        return success('验证成功', ['verify_token' => $token]);
    }

    #[
        OA\Post(
            path: '/api/user/phone/change',
            summary: '提交新手机号（两步验证版）',
            tags: ['用户/修改手机号信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'verify_token', type: 'string', description: '第一步签发的验证标识'),
                        new OA\Property(property: 'new_phone', type: 'string', description: '新手机号'),
                        new OA\Property(property: 'new_code', type: 'string', description: '新手机号验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '手机号修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function change(PhoneChangeRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->change($user, $request->input('verify_token'), $request->input('new_phone'), $request->input('new_code'));

        return success('手机号修改成功', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/user/phone/update-new',
            summary: '修改手机号（不验证旧手机号版）',
            tags: ['用户/修改手机号信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'new_phone', type: 'string', description: '新手机号'),
                        new OA\Property(property: 'new_code', type: 'string', description: '新手机号验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '手机号修改成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function updateNew(PhoneUpdateNewRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->updateNew($user, $request->input('new_phone'), $request->input('new_code'));

        return success('手机号修改成功', (new UserResource($result))->toArray($request));
    }
}
