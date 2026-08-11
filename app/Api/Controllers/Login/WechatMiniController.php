<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\BindPhoneRequest;
use App\Api\Requests\Login\WechatMiniLoginRequest;
use App\Api\Services\Login\WechatMiniService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class WechatMiniController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected WechatMiniService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/login/wechat-mini/login',
            summary: '微信小程序登录',
            tags: ['登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'code', type: 'string', description: 'wx.login 获取的 code'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '登录成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'token', type: 'string', example: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                    new OA\Property(property: 'user', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'account', type: 'string', example: 'wx_oABC123'),
                                            new OA\Property(property: 'nickname', type: 'string', example: '微信用户ABC123'),
                                        ]
                                    ),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function login(WechatMiniLoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->input('code'));

        return success('登录成功', $result);
    }

    #[
        OA\Post(
            path: '/api/login/wechat-mini/bind-phone',
            summary: '微信小程序绑定手机号',
            tags: ['登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'phone', type: 'string', description: '手机号'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '绑定成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'token', type: 'string', example: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                    new OA\Property(property: 'user', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'phone', type: 'string', example: '13800138000'),
                                        ]
                                    ),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function bindPhone(BindPhoneRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->bindPhone($user, $request->input('phone'), $request->input('code'));

        return success('绑定成功', $result);
    }
}
