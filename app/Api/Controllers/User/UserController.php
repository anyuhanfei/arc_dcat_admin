<?php

namespace App\Api\Controllers\User;

use App\Api\Requests\User\UserUpdateRequest;
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
            summary: '用户信息',
            tags: ['用户'],
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
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'account', type: 'string', example: 'user01'),
                                    new OA\Property(property: 'nickname', type: 'string', example: '用户0001'),
                                    new OA\Property(property: 'gender', type: 'string', example: '未知'),
                                    new OA\Property(property: 'age', type: 'integer', example: 25),
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

        $userInfo = $this->service->info($user);

        return success('success', $userInfo);
    }

    #[
        OA\Post(
            path: '/api/user/update',
            summary: '修改用户资料',
            tags: ['用户'],
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
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'nickname', type: 'string', example: '新昵称'),
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

        $userInfo = $this->service->update($request->user(), $params);

        return success('修改成功', $userInfo);
    }

    #[
        OA\Post(
            path: '/api/user/logout',
            summary: '退出登录',
            tags: ['用户'],
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
            tags: ['用户'],
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
