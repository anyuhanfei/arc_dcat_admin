<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

// 登录验证码刷新（无需登录，只带路由前缀不带中间件）
Route::prefix(config('admin.route.prefix'))
    ->withoutMiddleware(config('admin.route.middleware'))
    ->get('auth/captcha', [\App\Admin\Controllers\AuthController::class, 'getCaptcha']);

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('sys/agreements', \App\Admin\Controllers\Sys\SysAgreementsController::class);
    $router->resource('user/users', \App\Admin\Controllers\User\UsersController::class);
});
