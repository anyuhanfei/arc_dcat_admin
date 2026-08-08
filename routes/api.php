<?php

use Illuminate\Support\Facades\Route;

// 路由前缀 /api 由 bootstrap/app.php 中的 withRouting(api: ...) 自动添加

Route::post('sys/agreements/list', [\App\Api\Controllers\Sys\AgreementsController::class, 'index']);
Route::post('sys/agreements/detail', [\App\Api\Controllers\Sys\AgreementsController::class, 'show']);

// 登录相关（公开接口）
Route::prefix('login')->group(function () {
    Route::post('password/register', [\App\Api\Controllers\Login\PasswordController::class, 'register']);
    Route::post('password/login', [\App\Api\Controllers\Login\PasswordController::class, 'login']);
});

// 需登录接口
Route::middleware('auth:sanctum')->group(function () {
    Route::post('user/info', [\App\Api\Controllers\User\UserController::class, 'info']);
});
