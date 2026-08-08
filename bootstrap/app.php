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
    })->create();
