<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// 认证路由
Route::group(['prefix' => 'auth'], function (Illuminate\Contracts\Routing\Registrar $registrar) {
    $registrar->get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('admin.login');
    $registrar->post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('admin.login.auth');
    $registrar->post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('admin.logout');
});

// 后台首页
Route::get('', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('admin.main');
Route::get('index', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('admin.index');
Route::get('account', [\App\Http\Controllers\Admin\IndexController::class, 'account'])->name('admin.account');
Route::get('config', [\App\Http\Controllers\Admin\IndexController::class, 'config'])->name('admin.config');
Route::get('dashboard', [\App\Http\Controllers\Admin\IndexController::class, 'dashboard'])->name('admin.dashboard');
// 管理员管理
Route::get('admins/person', [\App\Http\Controllers\Admin\AdminController::class, 'person'])->name('admin.admins.person');
