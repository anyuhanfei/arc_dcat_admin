<?php

namespace App\Api\Controllers\Login;

use App\Api\Requests\Login\ForgotPasswordEmailRequest;
use App\Api\Requests\Login\ForgotPasswordPhoneRequest;
use App\Api\Requests\Login\PasswordLoginRequest;
use App\Api\Requests\Login\PasswordRegisterByEmailRequest;
use App\Api\Requests\Login\PasswordRegisterByPhoneRequest;
use App\Api\Resources\Login\LoginResource;
use App\Api\Resources\User\UserResource;
use App\Api\Services\Login\ForgotPasswordService;
use App\Api\Services\Login\PasswordService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PasswordController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected PasswordService $service,
        protected ForgotPasswordService $forgotPasswordService,
    ) {}

    #[
        OA\Post(
            path: '/api/login/password/login',
            summary: '账号密码登录',
            tags: ['登录/密码登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'account', type: 'string', description: '账号/手机号/邮箱'),
                        new OA\Property(property: 'password', type: 'string', description: '密码'),
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
                                            new OA\Property(property: 'account', type: 'string', example: 'user01'),
                                            new OA\Property(property: 'nickname', type: 'string', example: '用户0001'),
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
    public function login(PasswordLoginRequest $request): JsonResponse
    {
        $params = $request->input();

        $result = $this->service->login($params['account'], $params['password']);

        return success('登录成功', (new LoginResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/login/password/register-by-phone',
            summary: '账号密码注册（手机号）',
            tags: ['登录/密码登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'account', type: 'string', description: '账号（字母开头，字母数字下划线 4-20 位）'),
                        new OA\Property(property: 'password', type: 'string', description: '密码（至少6位）'),
                        new OA\Property(property: 'password_confirmation', type: 'string', description: '确认密码'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '注册成功'),
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
    public function registerByPhone(PasswordRegisterByPhoneRequest $request): JsonResponse
    {
        $params = $request->input();

        $result = $this->service->registerByPhone($params['account'], $params['password'], $params['phone'], $params['code']);

        return success('注册成功', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/login/password/register-by-email',
            summary: '账号密码注册（邮箱）',
            tags: ['登录/密码登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'account', type: 'string', description: '账号（字母开头，字母数字下划线 4-20 位）'),
                        new OA\Property(property: 'password', type: 'string', description: '密码（至少6位）'),
                        new OA\Property(property: 'password_confirmation', type: 'string', description: '确认密码'),
                        new OA\Property(property: 'email', type: 'string', description: '邮箱'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '注册成功'),
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
    public function registerByEmail(PasswordRegisterByEmailRequest $request): JsonResponse
    {
        $params = $request->input();

        $result = $this->service->registerByEmail($params['account'], $params['password'], $params['email'], $params['code']);

        return success('注册成功', (new UserResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/login/forgot-password/phone',
            summary: '忘记密码（手机号）',
            tags: ['登录/密码登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'phone', type: 'string', description: '手机号'),
                        new OA\Property(property: 'code', type: 'string', description: '短信验证码（6位数字）'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '密码重置成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function phone(ForgotPasswordPhoneRequest $request): JsonResponse
    {
        $this->forgotPasswordService->phone($request->input('phone'), $request->input('code'), $request->input('password'));

        return success('密码重置成功');
    }

    #[
        OA\Post(
            path: '/api/login/forgot-password/email',
            summary: '忘记密码（邮箱）',
            tags: ['登录/密码登录'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', description: '邮箱'),
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
                            new OA\Property(property: 'msg', type: 'string', example: '密码重置成功'),
                            new OA\Property(property: 'data', type: 'object', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function email(ForgotPasswordEmailRequest $request): JsonResponse
    {
        $this->forgotPasswordService->email($request->input('email'), $request->input('code'), $request->input('password'));

        return success('密码重置成功');
    }
}
