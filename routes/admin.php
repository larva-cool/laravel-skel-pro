<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AgreementController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AttachmentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CoinTradeController;
use App\Http\Controllers\Admin\DictController;
use App\Http\Controllers\Admin\IndexController;
use App\Http\Controllers\Admin\MailCodeController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PhoneCodeController;
use App\Http\Controllers\Admin\PointTradeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TaskGroupController;
use App\Http\Controllers\Admin\UploaderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\Facades\Route;

// 认证路由
Route::group(['prefix' => 'auth'], function (Registrar $registrar) {
    $registrar->get('login', [AuthController::class, 'showLoginForm'])->name('login');
    $registrar->post('login', [AuthController::class, 'login'])->name('login.auth');
    $registrar->post('logout', [AuthController::class, 'logout'])->name('logout');
});

// 后台首页
Route::get('', [IndexController::class, 'index'])->name('main');
Route::get('index', [IndexController::class, 'index'])->name('index');
Route::get('account', [IndexController::class, 'account'])->name('account');
Route::get('config', [IndexController::class, 'config'])->name('config');
Route::get('dashboard', [IndexController::class, 'dashboard'])->name('dashboard');

// RBAC
Route::get('roles/select', [RoleController::class, 'select'])->name('roles.select');
Route::resource('roles', RoleController::class, ['names' => 'roles'])->except(['show']);
Route::resource('permissions', PermissionController::class, ['names' => 'permissions'])->except(['show']);
Route::get('menus/select', [MenuController::class, 'menuSelect'])->name('menus.select');
Route::get('menus/left-menus', [MenuController::class, 'leftMenus'])->name('menus.left-menus');
Route::resource('menus', MenuController::class, ['names' => 'menus'])->except(['show']);

// 管理员管理
Route::get('admins/person', [AdminController::class, 'person'])->name('admins.person');
Route::post('admins/person', [AdminController::class, 'storePerson'])->name('admins.update_person');
Route::post('admins/password', [AdminController::class, 'storePassword'])->name('admins.update_password');
Route::post('admins/status', [AdminController::class, 'updateStatus'])->name('admins.status');
Route::post('admins/avatar/{admin}', [AdminController::class, 'updateAvatar'])->name('admins.avatar');
Route::resource('admins', AdminController::class, ['names' => 'admins'])->except(['show']);

// 系统设置
Route::get('settings/config', [SettingController::class, 'config'])->name('settings.config');
Route::post('settings/config', [SettingController::class, 'storeConfig'])->name('settings.config.store');
Route::resource('settings', SettingController::class, ['names' => 'settings'])->except(['show']);

// 字典管理
Route::post('dicts/status', [DictController::class, 'updateStatus'])->name('dicts.status');
Route::post('dicts/store_data', [DictController::class, 'storeData'])->name('dicts.store_data');
Route::post('dicts/batch_destroy', [DictController::class, 'batchDestroy'])->name('dicts.batch_destroy');
Route::get('dicts/create_data', [DictController::class, 'createData'])->name('dicts.create_data');
Route::get('dicts/edit_data/{dict}', [DictController::class, 'editData'])->name('dicts.edit_data');
Route::resource('dicts', DictController::class, ['names' => 'dicts'])->except(['show']);

// 地区管理
Route::get('areas/select', [AreaController::class, 'select'])->name('areas.select');
Route::get('areas/children', [AreaController::class, 'children'])->name('areas.children');
Route::resource('areas', AreaController::class, ['names' => 'areas'])->except(['show']);

// 附件管理
Route::resource('attachments', AttachmentController::class, ['names' => 'attachments'])->except(['edit', 'show']);

// 文件上传
Route::post('uploader/tinymce', [UploaderController::class, 'tinymce'])->name('uploader.tinymce');
Route::post('uploader/aieditor-file', [UploaderController::class, 'aiEditorFile'])->name('uploader.aieditor-file');
Route::post('uploader/aieditor-video', [UploaderController::class, 'aiEditorVideo'])->name('uploader.aieditor-video');
Route::post('uploader/aieditor-image', [UploaderController::class, 'aiEditorImage'])->name('uploader.aieditor-image');
Route::post('uploader/image', [UploaderController::class, 'image'])->name('uploader.image');

// 用户协议
Route::resource('agreements', AgreementController::class, ['names' => 'agreements'])->except(['show']);

// 公告管理
Route::post('announcements/status', [AnnouncementController::class, 'updateStatus'])->name('announcements.status');
Route::resource('announcements', AnnouncementController::class, ['names' => 'announcements'])->except(['show']);

// 用户组管理
Route::get('user_groups/select', [UserGroupController::class, 'select'])->name('user_groups.select');
Route::resource('user_groups', UserGroupController::class, ['names' => 'user_groups']);

// 用户管理
Route::post('users/status', [UserController::class, 'updateStatus'])->name('users.status');
Route::resource('users', UserController::class, ['names' => 'users']);

// 任务管理
Route::post('task_groups/{task_group}/status', [TaskGroupController::class, 'updateStatus'])->name('task_groups.status');
Route::post('task_groups/{task_group}/repair', [TaskGroupController::class, 'repair'])->name('task_groups.repair');
Route::resource('task_groups', TaskGroupController::class, ['names' => 'task_groups']);
Route::resource('task_groups.tasks', TaskController::class, ['names' => 'task_groups.tasks'])->except(['show']);

// 金币记录
Route::get('coins', [CoinTradeController::class, 'index'])->name('coins.index');
Route::get('points', [PointTradeController::class, 'index'])->name('points.index');

// 短信验证码记录
Route::get('phone_codes', [PhoneCodeController::class, 'index'])->name('phone_codes.index');

// 邮件验证码记录
Route::get('mail_codes', [MailCodeController::class, 'index'])->name('mail_codes.index');

// 单页管理
Route::resource('pages', PageController::class, ['names' => 'pages'])->except(['show']);

// 反馈管理
Route::resource('feedbacks', \App\Http\Controllers\Admin\FeedbackController::class, ['names' => 'feedbacks'])->only(['index', 'edit', 'update', 'destroy']);

// 举报管理
Route::resource('reports', \App\Http\Controllers\Admin\ReportController::class, ['names' => 'reports'])->only(['index', 'edit', 'update', 'destroy']);
