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

/**
 * Ajax
 */
Route::group(['prefix' => 'ajax','as'=>'ajax.'], function (Illuminate\Contracts\Routing\Registrar $registrar) {
    $registrar->get('menus', [\App\Http\Controllers\Admin\AjaxController::class, 'menus'])->name('menus');
    $registrar->get('permission', [\App\Http\Controllers\Admin\AjaxController::class, 'permission'])->name('permission');
    $registrar->get('menu-select', [\App\Http\Controllers\Admin\AjaxController::class, 'menuSelect'])->name('menu-select');
});

// 后台首页
Route::get('', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('main');
Route::get('index', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('index');
Route::get('account', [\App\Http\Controllers\Admin\IndexController::class, 'account'])->name('account');
Route::get('config', [\App\Http\Controllers\Admin\IndexController::class, 'config'])->name('config');
Route::get('dashboard', [\App\Http\Controllers\Admin\IndexController::class, 'dashboard'])->name('dashboard');

Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class, ['names' => 'menus'])->except(['show']);
Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class, ['names' => 'roles'])->except(['show']);
Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class, ['names' => 'permissions'])->except(['show']);
Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class, ['names' => 'admins'])->except(['show']);
Route::get('permissions/routes', [\App\Http\Controllers\Admin\PermissionController::class, 'getRoutes'])->name('routes');
Route::get('admins/person', [\App\Http\Controllers\Admin\AdminController::class, 'person'])->name('admins.person');




