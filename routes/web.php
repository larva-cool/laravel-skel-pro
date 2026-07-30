<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\MainController::class, 'index']);
Route::get('redirect', [\App\Http\Controllers\MainController::class, 'redirect']);

// 认证失败时的默认跳转路由（保证 auth:sanctum 或 api 认证失败时返回 JSON 401）
Route::get('login', function (): JsonResponse {
    return response()->json([
        'code' => 401,
        'message' => '登录已过期，请重新登录',
        'data' => null,
    ], 401);
})->name('login');
