<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\UserUpdateRequest;
use App\Api\Resources\User\UserResource;
use App\Api\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected UserService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/user/info',
            summary: '获取用户信息',
            description: '获取当前登录用户的完整资料（11 个字段），用于"我的"页面和资料页展示。',
            tags: ['用户/用户信息'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13800138000'),
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
    public function info(Request $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->info($user);

        return success('success', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/user/update',
            summary: '修改用户资料',
            description: '修改当前登录用户的头像/昵称/个性签名/性别/生日；所有字段均可选，传哪个改哪个。返回更新后的完整用户资料。',
            tags: ['用户/用户信息'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'avatar', type: 'string', description: '头像（可选）'),
                        new OA\Property(property: 'nickname', type: 'string', description: '昵称（可选，最长50字符）'),
                        new OA\Property(property: 'signature', type: 'string', description: '个性签名（可选，最长255字符）'),
                        new OA\Property(property: 'gender', type: 'integer', description: '性别（可选，1男 2女）'),
                        new OA\Property(property: 'birthday', type: 'string', description: '生日（可选，YYYY-MM-DD，不能是未来日期）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '修改成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: '用户ID', example: 1),
                                    new OA\Property(property: 'account', type: 'string', description: '用户账号（字母数字下划线；微信/Apple 登录为 wx_xxx/apple_xxx）', example: 'u_10001'),
                                    new OA\Property(property: 'phone', type: 'string', description: '手机号（未绑定为空字符串）', example: '13800138000'),
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
    public function update(UserUpdateRequest $request): JsonResponse
    {
        $params = $request->only(['avatar', 'nickname', 'signature', 'gender', 'birthday']);

        $result = $this->service->update($request->user(), $params);

        return success('修改成功', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/user/logout',
            summary: '退出登录',
            description: '吊销当前 Sanctum token，前端清除本地 token 后跳转登录页。',
            tags: ['用户/用户信息'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: '退出成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());

        return success('退出成功');
    }

    #[
        OA\Post(
            path: '/api/user/cancel',
            summary: '注销账号',
            description: '永久注销当前账号（软删除），注销后不可恢复；调用前前端必须二次确认。',
            tags: ['用户/用户信息'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: '注销成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function cancel(Request $request): JsonResponse
    {
        $this->service->cancel($request->user());

        return success('注销成功');
    }
}
