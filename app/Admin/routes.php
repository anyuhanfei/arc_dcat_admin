<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

// 自定义表单字段注册：媒体库选择器（App\Admin\Form\Field\MediaSelector）
\Dcat\Admin\Form::extend('mediaSelector', \App\Admin\Form\Field\MediaSelector::class);

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
    $router->get('media-selector/list', [\App\Admin\Controllers\MediaSelectorController::class, 'list']);
    $router->post('media-selector/upload', [\App\Admin\Controllers\MediaSelectorController::class, 'upload']);

    $router->resource('sys/agreements', \App\Admin\Controllers\Sys\SysAgreementsController::class);
    $router->resource('sys/faq-categories', \App\Admin\Controllers\Sys\SysFaqCategoriesController::class);
    $router->resource('sys/faq-questions', \App\Admin\Controllers\Sys\SysFaqQuestionsController::class);
    $router->resource('sys/article-categories', \App\Admin\Controllers\Sys\SysArticleCategoriesController::class);
    $router->resource('sys/articles', \App\Admin\Controllers\Sys\SysArticlesController::class);
    $router->resource('sys/notices', \App\Admin\Controllers\Sys\SysNoticesController::class);
    $router->get('sys/notice-reads', \App\Admin\Controllers\Sys\SysNoticeReadsController::class.'@index');

    $router->resource('user/users', \App\Admin\Controllers\User\UsersController::class);

});
