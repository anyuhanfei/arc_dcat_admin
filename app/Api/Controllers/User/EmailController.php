<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\EmailChangeRequest;
use App\Api\Requests\User\EmailUpdateNewRequest;
use App\Api\Requests\User\EmailUpdateRequest;
use App\Api\Requests\User\EmailVerifyOldRequest;
use App\Api\Resources\User\UserResource;
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
            description: '同时验证旧邮箱和新邮箱验证码，一次提交完成换绑；返回换绑后的完整用户资料。',
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
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13800138000'),
                                    new OA\Property(property: 'email', type: 'string', description: '邮箱（修改后的新邮箱）', example: 'user@example.com'),
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
    public function update(EmailUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->update($user, $request->input('old_email'), $request->input('old_code'), $request->input('new_email'), $request->input('new_code'));

        return success('邮箱修改成功', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/user/email/verify-old',
            summary: '验证旧邮箱（两步验证版）',
            description: '两步换绑第一步：校验旧邮箱验证码通过后下发 verify_token，第二步用该 token 提交新邮箱。',
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
            description: '两步换绑第二步：带上第一步签发的 verify_token + 新邮箱验证码完成换绑；返回换绑后的完整用户资料。',
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
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13800138000'),
                                    new OA\Property(property: 'email', type: 'string', description: '邮箱（修改后的新邮箱）', example: 'user@example.com'),
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
    public function change(EmailChangeRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->change($user, $request->input('verify_token'), $request->input('new_email'), $request->input('new_code'));

        return success('邮箱修改成功', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/user/email/update-new',
            summary: '修改邮箱（不验证旧邮箱版）',
            description: '仅校验新邮箱验证码即可换绑，不校验旧邮箱；适合旧邮箱已停用的场景。返回换绑后的完整用户资料。',
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
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13800138000'),
                                    new OA\Property(property: 'email', type: 'string', description: '邮箱（修改后的新邮箱）', example: 'user@example.com'),
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
    public function updateNew(EmailUpdateNewRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->updateNew($user, $request->input('new_email'), $request->input('new_code'));

        return success('邮箱修改成功', (new UserResource($result))->toArray($request));
    }
}
