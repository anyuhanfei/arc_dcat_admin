<?php

use App\Exceptions\BusinessException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 未认证请求不重定向到命名路由（API 无 login 路由），直接抛 AuthenticationException
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // API 未登录：返回统一格式（code=0, error=no_login, HTTP 200）
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return error('未登录', NO_LOGIN);
            }

            return null;
        });

        // API 业务异常：返回统一格式（code=0, error=业务错误码）
        $exceptions->render(function (BusinessException $e, Request $request) {
            if ($request->is('api/*')) {
                return error($e->getMessage(), (string) $e->getError());
            }

            return null;
        });

        // API 表单验证失败：返回统一格式（code=0, msg=第一条错误, errors=字段错误明细）
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                $errors = $e->errors();
                $firstMessage = collect($errors)->flatten()->first() ?: '参数校验失败';
                return error($firstMessage, '', $errors);
            }
            return null;
        });

        // API 授权失败（403）
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return error('无权限执行此操作');
            }
            return null;
        });

        // API 资源不存在（404 Model not found）
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return error('请求的资源不存在');
            }
            return null;
        });
    })->create();
