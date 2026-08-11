<?php

use Illuminate\Support\Facades\Route;

// 路由前缀 /api 由 bootstrap/app.php 中的 withRouting(api: ...) 自动添加

Route::post('sys/agreements/list', [\App\Api\Controllers\Sys\AgreementsController::class, 'index']);
Route::post('sys/agreements/detail', [\App\Api\Controllers\Sys\AgreementsController::class, 'show']);

// 登录相关（公开接口）
Route::prefix('login')->group(function () {
    Route::post('password/register', [\App\Api\Controllers\Login\PasswordController::class, 'register']);
    Route::post('password/login', [\App\Api\Controllers\Login\PasswordController::class, 'login']);
    Route::post('password/register-by-phone', [\App\Api\Controllers\Login\PasswordController::class, 'registerByPhone']);
    Route::post('password/register-by-email', [\App\Api\Controllers\Login\PasswordController::class, 'registerByEmail']);
    Route::post('phone/login', [\App\Api\Controllers\Login\PhoneController::class, 'login']);
    Route::post('email/login', [\App\Api\Controllers\Login\EmailController::class, 'login']);
    Route::post('wechat-mini/login', [\App\Api\Controllers\Login\WechatMiniController::class, 'login']);
    Route::post('wechat-mp/login', [\App\Api\Controllers\Login\WechatMpController::class, 'login']);
    Route::post('wechat-app/login', [\App\Api\Controllers\Login\WechatAppController::class, 'login']);
});

// 短信相关（公开接口，短信不只登录使用）
Route::post('sms/send', [\App\Api\Controllers\Ms\SmsController::class, 'send']);

// 邮件相关（公开接口）
Route::post('email/send', [\App\Api\Controllers\Ms\EmailController::class, 'send']);

// 需登录接口
Route::middleware('auth:sanctum')->group(function () {
    Route::post('user/info', [\App\Api\Controllers\User\UserController::class, 'info']);
});

// 微信绑定手机号（需登录，统一在 login 前缀下）
Route::prefix('login')->middleware('auth:sanctum')->group(function () {
    Route::post('wechat-mini/bind-phone', [\App\Api\Controllers\Login\WechatMiniController::class, 'bindPhone']);
    Route::post('wechat-mp/bind-phone', [\App\Api\Controllers\Login\WechatMpController::class, 'bindPhone']);
    Route::post('wechat-app/bind-phone', [\App\Api\Controllers\Login\WechatAppController::class, 'bindPhone']);
});
