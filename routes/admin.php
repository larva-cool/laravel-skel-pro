<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\Facades\Route;

// 认证路由
Route::group(['prefix' => 'auth'], function (Registrar $registrar) {
    $registrar->post('login', [AuthController::class, 'login'])->name('login');
    $registrar->post('logout', [AuthController::class, 'logout'])->name('logout');
    $registrar->get('info', [AuthController::class, 'info'])->name('info');
});

// 角色管理
Route::get('roles/permissions', [RoleController::class, 'allPermissions'])->name('roles.permissions');
Route::get('roles/{id}/permissions', [RoleController::class, 'permissions'])->name('roles.get-permissions');
Route::put('roles/{id}/permissions', [RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');
Route::apiResource('roles', RoleController::class);

