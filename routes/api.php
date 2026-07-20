<?php

use Illuminate\Support\Facades\Route;

// 路由前缀 /api 由 bootstrap/app.php 中的 withRouting(api: ...) 自动添加

Route::post('sys/agreements/list', [\App\Api\Controllers\Sys\AgreementsController::class, 'index']);
Route::post('sys/agreements/detail', [\App\Api\Controllers\Sys\AgreementsController::class, 'show']);
