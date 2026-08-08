<?php

namespace App\Api\Controllers\User;

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
}
