<?php

use Illuminate\Support\Facades\Route;

// 路由前缀 /api 由 bootstrap/app.php 中的 withRouting(api: ...) 自动添加

Route::post('sys/agreements/list', [\App\Api\Controllers\Sys\AgreementsController::class, 'index']);
Route::post('sys/agreements/detail', [\App\Api\Controllers\Sys\AgreementsController::class, 'show']);

// 常见问题相关（公开接口）
Route::post('sys/faq/categories', [\App\Api\Controllers\Sys\FaqController::class, 'categories']);
Route::post('sys/faq/questions', [\App\Api\Controllers\Sys\FaqController::class, 'questions']);

// 轮播图相关（公开接口）
Route::post('sys/banners/list', [\App\Api\Controllers\Sys\BannersController::class, 'index']);

// 文章相关（公开接口）
Route::post('sys/articles/categories', [\App\Api\Controllers\Sys\ArticlesController::class, 'categories']);
Route::post('sys/articles/list', [\App\Api\Controllers\Sys\ArticlesController::class, 'index']);
Route::post('sys/articles/detail', [\App\Api\Controllers\Sys\ArticlesController::class, 'show']);

// 公告相关（公开接口）
Route::post('sys/notices/list', [\App\Api\Controllers\Sys\NoticesController::class, 'index']);
Route::post('sys/notices/detail', [\App\Api\Controllers\Sys\NoticesController::class, 'show']);
Route::post('sys/notices/latest', [\App\Api\Controllers\Sys\NoticesController::class, 'latest']);

// 系统配置相关（公开接口）
Route::post('sys/configs', [\App\Api\Controllers\Sys\ConfigsController::class, 'index']);

// 登录相关（公开接口）
Route::prefix('login')->group(function () {
    Route::post('password/login', [\App\Api\Controllers\Login\PasswordController::class, 'login']);
    Route::post('password/register-by-phone', [\App\Api\Controllers\Login\PasswordController::class, 'registerByPhone']);
    Route::post('password/register-by-email', [\App\Api\Controllers\Login\PasswordController::class, 'registerByEmail']);
    Route::post('phone/login', [\App\Api\Controllers\Login\PhoneController::class, 'login']);
    Route::post('email/login', [\App\Api\Controllers\Login\EmailController::class, 'login']);
    Route::post('wechat-mini/login', [\App\Api\Controllers\Login\WechatMiniController::class, 'login']);
    Route::post('wechat-mp/login', [\App\Api\Controllers\Login\WechatMpController::class, 'login']);
    Route::post('wechat-app/login', [\App\Api\Controllers\Login\WechatAppController::class, 'login']);
    Route::post('apple/login', [\App\Api\Controllers\Login\AppleController::class, 'login']);
    Route::post('yidun/login', [\App\Api\Controllers\Login\YidunController::class, 'login']);
    Route::post('forgot-password/phone', [\App\Api\Controllers\Login\PasswordController::class, 'phone']);
    Route::post('forgot-password/email', [\App\Api\Controllers\Login\PasswordController::class, 'email']);
});

// 短信相关（公开接口，短信不只登录使用）
Route::post('sms/send', [\App\Api\Controllers\Ms\SmsController::class, 'send']);

// 邮件相关（公开接口）
Route::post('email/send', [\App\Api\Controllers\Ms\EmailController::class, 'send']);

// 需登录接口
Route::middleware('auth:sanctum')->group(function () {
    Route::post('user/info', [\App\Api\Controllers\User\UserController::class, 'info']);
    Route::post('user/update', [\App\Api\Controllers\User\UserController::class, 'update']);
    Route::post('user/logout', [\App\Api\Controllers\User\UserController::class, 'logout']);
    Route::post('user/cancel', [\App\Api\Controllers\User\UserController::class, 'cancel']);
    Route::post('user/password/phone/update', [\App\Api\Controllers\User\PasswordPhoneController::class, 'update']);
    Route::post('user/password/phone/verify', [\App\Api\Controllers\User\PasswordPhoneController::class, 'verify']);
    Route::post('user/password/phone/change', [\App\Api\Controllers\User\PasswordPhoneController::class, 'change']);
    Route::post('user/password/email/update', [\App\Api\Controllers\User\PasswordEmailController::class, 'update']);
    Route::post('user/password/email/verify', [\App\Api\Controllers\User\PasswordEmailController::class, 'verify']);
    Route::post('user/password/email/change', [\App\Api\Controllers\User\PasswordEmailController::class, 'change']);
    Route::post('user/password/old/update', [\App\Api\Controllers\User\PasswordOldController::class, 'update']);
    Route::post('user/password/old/verify', [\App\Api\Controllers\User\PasswordOldController::class, 'verify']);
    Route::post('user/password/old/change', [\App\Api\Controllers\User\PasswordOldController::class, 'change']);
    Route::post('user/phone/update', [\App\Api\Controllers\User\PhoneController::class, 'update']);
    Route::post('user/phone/verify-old', [\App\Api\Controllers\User\PhoneController::class, 'verifyOld']);
    Route::post('user/phone/change', [\App\Api\Controllers\User\PhoneController::class, 'change']);
    Route::post('user/phone/update-new', [\App\Api\Controllers\User\PhoneController::class, 'updateNew']);
    Route::post('user/email/update', [\App\Api\Controllers\User\EmailController::class, 'update']);
    Route::post('user/email/verify-old', [\App\Api\Controllers\User\EmailController::class, 'verifyOld']);
    Route::post('user/email/change', [\App\Api\Controllers\User\EmailController::class, 'change']);
    Route::post('user/email/update-new', [\App\Api\Controllers\User\EmailController::class, 'updateNew']);
});

// 微信绑定手机号（需登录，统一在 login 前缀下）
Route::prefix('login')->middleware('auth:sanctum')->group(function () {
    Route::post('wechat-mini/bind-phone', [\App\Api\Controllers\Login\WechatMiniController::class, 'bindPhone']);
    Route::post('wechat-mp/bind-phone', [\App\Api\Controllers\Login\WechatMpController::class, 'bindPhone']);
    Route::post('wechat-app/bind-phone', [\App\Api\Controllers\Login\WechatAppController::class, 'bindPhone']);
    Route::post('apple/bind-phone', [\App\Api\Controllers\Login\AppleController::class, 'bindPhone']);
});
