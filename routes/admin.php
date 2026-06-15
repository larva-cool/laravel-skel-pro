<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\Facades\Route;

// 认证路由
Route::group(['prefix' => 'auth'], function (Registrar $registrar) {
    $registrar->get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('login');
    $registrar->post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.auth');
    $registrar->post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
});

// 后台首页
Route::get('', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('main');
Route::get('index', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('index');
Route::get('account', [\App\Http\Controllers\Admin\IndexController::class, 'account'])->name('account');
Route::get('config', [\App\Http\Controllers\Admin\IndexController::class, 'config'])->name('config');
Route::get('dashboard', [\App\Http\Controllers\Admin\IndexController::class, 'dashboard'])->name('dashboard');

// RBAC
Route::get('roles/select', [\App\Http\Controllers\Admin\RoleController::class, 'select'])->name('roles.select');
Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class, ['names' => 'roles'])->except(['show']);
Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class, ['names' => 'permissions'])->except(['show']);
Route::get('menus/select', [\App\Http\Controllers\Admin\MenuController::class, 'menuSelect'])->name('menus.select');
Route::get('menus/left-menus', [\App\Http\Controllers\Admin\MenuController::class, 'leftMenus'])->name('menus.left-menus');
Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class, ['names' => 'menus'])->except(['show']);

// 管理员管理
Route::get('admins/person', [\App\Http\Controllers\Admin\AdminController::class, 'person'])->name('admins.person');
Route::post('admins/person', [\App\Http\Controllers\Admin\AdminController::class, 'storePerson'])->name('admins.update_person');
Route::post('admins/password', [\App\Http\Controllers\Admin\AdminController::class, 'storePassword'])->name('admins.update_password');
Route::post('admins/status', [\App\Http\Controllers\Admin\AdminController::class, 'updateStatus'])->name('admins.status');
Route::post('admins/avatar/{admin}', [\App\Http\Controllers\Admin\AdminController::class, 'updateAvatar'])->name('admins.avatar');
Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class, ['names' => 'admins'])->except(['show']);

// 系统设置
Route::get('settings/config', [\App\Http\Controllers\Admin\SettingController::class, 'config'])->name('settings.config');
Route::post('settings/config', [\App\Http\Controllers\Admin\SettingController::class, 'storeConfig'])->name('settings.config.store');
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
Route::get('areas/children', [\App\Http\Controllers\Admin\AreaController::class, 'children'])->name('areas.children');
Route::resource('areas', \App\Http\Controllers\Admin\AreaController::class, ['names' => 'areas'])->except(['show']);

// 附件管理
Route::resource('attachments', \App\Http\Controllers\Admin\AttachmentController::class, ['names' => 'attachments'])->except(['edit', 'show']);

// 文件上传
Route::post('uploader/tinymce', [\App\Http\Controllers\Admin\UploaderController::class, 'tinymce'])->name('uploader.tinymce');
Route::post('uploader/aieditor-file', [\App\Http\Controllers\Admin\UploaderController::class, 'aiEditorFile'])->name('uploader.aieditor-file');
Route::post('uploader/aieditor-video', [\App\Http\Controllers\Admin\UploaderController::class, 'aiEditorVideo'])->name('uploader.aieditor-video');
Route::post('uploader/aieditor-image', [\App\Http\Controllers\Admin\UploaderController::class, 'aiEditorImage'])->name('uploader.aieditor-image');

// 用户协议
Route::resource('agreements', \App\Http\Controllers\Admin\AgreementController::class, ['names' => 'agreements'])->except(['show']);

// 公告管理
Route::post('announcements/status', [\App\Http\Controllers\Admin\AnnouncementController::class, 'updateStatus'])->name('announcements.status');
Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class, ['names' => 'announcements'])->except(['show']);

// 用户组管理
Route::get('user_groups/select', [\App\Http\Controllers\Admin\UserGroupController::class, 'select'])->name('user_groups.select');
Route::resource('user_groups', \App\Http\Controllers\Admin\UserGroupController::class, ['names' => 'user_groups']);

// 用户管理
Route::post('users/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('users.status');
Route::resource('users', \App\Http\Controllers\Admin\UserController::class, ['names' => 'users']);

// 金币记录
Route::get('coins', [\App\Http\Controllers\Admin\CoinTradeController::class, 'index'])->name('coins.index');
Route::get('points', [\App\Http\Controllers\Admin\PointTradeController::class, 'index'])->name('points.index');
// 单页管理
Route::resource('pages', \App\Http\Controllers\Admin\PageController::class, ['names' => 'pages'])->except(['show']);

// 反馈管理
//Route::resource('feedback', \App\Http\Controllers\Admin\FeedbackController::class, ['names' => 'feedback'])->only(['index', 'destroy']);
