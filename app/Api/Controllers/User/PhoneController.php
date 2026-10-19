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
            description: '同时验证旧手机号和新手机号验证码，一次提交完成换绑；返回换绑后的完整用户资料。',
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
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（修改后的新手机号）', example: '13800138000'),
                                    new OA\Property(property: 'email', type: 'string', description: '邮箱（未绑定为空字符串）', example: 'user@example.com'),
                                    new OA\Property(property: 'nickname', type: 'string', description: '昵称', example: '张三'),
                                    new OA\Property(property: 'avatar', type: 'string', description: '头像完整 URL（无头像为空字符串）', example: 'https://example.com/uploads/avatar.jpg'),
                                    new OA\Property(property: 'signature', type: 'string', description: '个性签名（未设置为空字符串）', example: '这个人很懒，什么都没写'),
                                    new OA\Property(property: 'gender', type: 'string', description: '性别：男/女/未知', example: '男'),
                                    new OA\Property(property: 'age', type: 'integer', description: '年龄（根据生日计算，未设置生日为 0）', example: 25),
                                    new OA\Property(property: 'birthday', type: 'string', description: '生日 Y-m-d（未设置为空字符串）', example: '2000-01-15'),
                                    new OA\Property(property: 'created_at', type: 'string', description: '注册时间 Y-m-d H:i:s', example: '2026-10-01 12:00:00'),
                                ]
                            ),
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
            description: '两步换绑第一步：校验旧手机号验证码通过后下发 verify_token，第二步用该 token 提交新手机号。',
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
            description: '两步换绑第二步：带上第一步签发的 verify_token + 新手机号验证码完成换绑；返回换绑后的完整用户资料。',
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
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（修改后的新手机号）', example: '13800138000'),
                                    new OA\Property(property: 'email', type: 'string', description: '邮箱（未绑定为空字符串）', example: 'user@example.com'),
                                    new OA\Property(property: 'nickname', type: 'string', description: '昵称', example: '张三'),
                                    new OA\Property(property: 'avatar', type: 'string', description: '头像完整 URL（无头像为空字符串）', example: 'https://example.com/uploads/avatar.jpg'),
                                    new OA\Property(property: 'signature', type: 'string', description: '个性签名（未设置为空字符串）', example: '这个人很懒，什么都没写'),
                                    new OA\Property(property: 'gender', type: 'string', description: '性别：男/女/未知', example: '男'),
                                    new OA\Property(property: 'age', type: 'integer', description: '年龄（根据生日计算，未设置生日为 0）', example: 25),
                                    new OA\Property(property: 'birthday', type: 'string', description: '生日 Y-m-d（未设置为空字符串）', example: '2000-01-15'),
                                    new OA\Property(property: 'created_at', type: 'string', description: '注册时间 Y-m-d H:i:s', example: '2026-10-01 12:00:00'),
                                ]
                            ),
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
            description: '仅校验新手机号验证码即可换绑，不校验旧手机号；适合旧手机号已停用的场景。返回换绑后的完整用户资料。',
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
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（修改后的新手机号）', example: '13800138000'),
                                    new OA\Property(property: 'email', type: 'string', description: '邮箱（未绑定为空字符串）', example: 'user@example.com'),
                                    new OA\Property(property: 'nickname', type: 'string', description: '昵称', example: '张三'),
                                    new OA\Property(property: 'avatar', type: 'string', description: '头像完整 URL（无头像为空字符串）', example: 'https://example.com/uploads/avatar.jpg'),
                                    new OA\Property(property: 'signature', type: 'string', description: '个性签名（未设置为空字符串）', example: '这个人很懒，什么都没写'),
                                    new OA\Property(property: 'gender', type: 'string', description: '性别：男/女/未知', example: '男'),
                                    new OA\Property(property: 'age', type: 'integer', description: '年龄（根据生日计算，未设置生日为 0）', example: 25),
                                    new OA\Property(property: 'birthday', type: 'string', description: '生日 Y-m-d（未设置为空字符串）', example: '2000-01-15'),
                                    new OA\Property(property: 'created_at', type: 'string', description: '注册时间 Y-m-d H:i:s', example: '2026-10-01 12:00:00'),
                                ]
                            ),
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
