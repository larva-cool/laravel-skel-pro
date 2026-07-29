<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\Facades\Route;

// 认证路由
Route::group(['prefix' => 'auth'], function (Registrar $registrar) {
    $registrar->post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login');
    $registrar->post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
    $registrar->get('info', [\App\Http\Controllers\Admin\AuthController::class, 'info'])->name('info');
});

