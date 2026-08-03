<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
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

// 前端路由配置
Route::get('routes', [MainController::class, 'routes'])->name('routes');

// 菜单管理
Route::apiResource('menus', MenuController::class);

// 管理员管理
Route::get('admins/profile', [AdminController::class, 'profile'])->name('admins.profile');
Route::put('admins/profile', [AdminController::class, 'updateProfile'])->name('admins.update-profile');
Route::get('admins/{id}/roles', [AdminController::class, 'roles'])->name('admins.roles');
Route::put('admins/{id}/roles', [AdminController::class, 'assignRoles'])->name('admins.assign-roles');
Route::put('admins/{id}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admins.toggle-status');
Route::put('admins/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('admins.reset-password');
Route::put('admins/change-password', [AdminController::class, 'changePassword'])->name('admins.change-password');
Route::apiResource('admins', AdminController::class);

// 配置管理
Route::apiResource('settings', SettingController::class);

// 地区管理
Route::apiResource('areas', AreaController::class);

