<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// 认证路由
Route::group(['prefix' => 'auth'], function (Illuminate\Contracts\Routing\Registrar $registrar) {
    $registrar->get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('login');
    $registrar->post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.auth');
    $registrar->post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
});

/**
 * Ajax
 */
Route::group(['prefix' => 'ajax','as'=>'ajax.'], function (Illuminate\Contracts\Routing\Registrar $registrar) {
    $registrar->get('left-menus', [\App\Http\Controllers\Admin\AjaxController::class, 'leftMenus'])->name('left-menus');
    $registrar->get('permission', [\App\Http\Controllers\Admin\AjaxController::class, 'permission'])->name('permission');
    $registrar->get('menu-select', [\App\Http\Controllers\Admin\AjaxController::class, 'menuSelect'])->name('menu-select');// 菜单选择
    $registrar->get('role-select', [\App\Http\Controllers\Admin\AjaxController::class, 'roleSelect'])->name('role-select');// 角色选择
});

// 后台首页
Route::get('', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('main');
Route::get('index', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('index');
Route::get('account', [\App\Http\Controllers\Admin\IndexController::class, 'account'])->name('account');
Route::get('config', [\App\Http\Controllers\Admin\IndexController::class, 'config'])->name('config');
Route::get('dashboard', [\App\Http\Controllers\Admin\IndexController::class, 'dashboard'])->name('dashboard');

// 管理员
Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class, ['names' => 'menus'])->except(['show']);
Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class, ['names' => 'roles'])->except(['show']);
Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class, ['names' => 'permissions'])->except(['show']);

// 管理员管理
Route::get('admins/person', [\App\Http\Controllers\Admin\AdminController::class, 'person'])->name('admins.person');
Route::post('admins/person', [\App\Http\Controllers\Admin\AdminController::class, 'storePerson'])->name('admins.update_person');
Route::post('admins/password', [\App\Http\Controllers\Admin\AdminController::class, 'storePassword'])->name('admins.update_password');
Route::post('admins/status', [\App\Http\Controllers\Admin\AdminController::class, 'updateStatus'])->name('admins.status');
Route::post('admins/avatar/{admin}', [\App\Http\Controllers\Admin\AdminController::class, 'updateAvatar'])->name('admins.avatar');
Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class, ['names' => 'admins'])->except(['show']);
Route::get('permissions/routes', [\App\Http\Controllers\Admin\PermissionController::class, 'getRoutes'])->name('routes');

// 系统设置
Route::get('system-config', [\App\Http\Controllers\Admin\SettingController::class, 'config'])->name('system-config');
Route::post('system-config', [\App\Http\Controllers\Admin\SettingController::class, 'storeConfig'])->name('system-config.store');
Route::resource('settings', \App\Http\Controllers\Admin\SettingController::class, ['names' => 'settings'])->except(['show']);

// 字典管理
Route::post('dicts/status', [\App\Http\Controllers\Admin\DictController::class, 'updateStatus'])->name('dicts.status');
Route::post('dicts/store_data', [\App\Http\Controllers\Admin\DictController::class, 'storeData'])->name('dicts.stoer_data');
Route::post('dicts/batch_destroy', [\App\Http\Controllers\Admin\DictController::class, 'batchDestroy'])->name('dicts.batch_destroy');
Route::get('dicts/create_data', [\App\Http\Controllers\Admin\DictController::class, 'createData'])->name('dicts.create_data');
Route::get('dicts/edit_data/{dict}', [\App\Http\Controllers\Admin\DictController::class, 'editData'])->name('dicts.edit_data');
Route::resource('dicts', \App\Http\Controllers\Admin\DictController::class, ['names' => 'dicts'])->except(['show']);

// 地区管理
Route::get('areas/select', [\App\Http\Controllers\Admin\AreaController::class, 'select'])->name('areas.select');
Route::resource('areas', \App\Http\Controllers\Admin\AreaController::class, ['names' => 'areas'])->except(['show']);


