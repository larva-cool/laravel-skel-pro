<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\MailCodeController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PhoneCodeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UploaderController;
use App\Http\Controllers\Admin\UserController;
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
Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.get-permissions');
Route::put('roles/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');
Route::apiResource('roles', RoleController::class);

// 前端路由配置
Route::get('routes', [MainController::class, 'routes'])->name('routes');

// 通知管理
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
Route::put('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
Route::put('notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
Route::delete('notifications/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');

// 菜单管理
Route::apiResource('menus', MenuController::class);

// 管理员管理
Route::get('admins/profile', [AdminController::class, 'profile'])->name('admins.profile');
Route::put('admins/profile', [AdminController::class, 'updateProfile'])->name('admins.update-profile');
Route::post('admins/avatar', [AdminController::class, 'updateAvatar'])->name('admins.update-avatar');
Route::get('admins/{admin}/roles', [AdminController::class, 'roles'])->name('admins.roles');
Route::get('admins/{admin}/login-histories', [AdminController::class, 'loginHistories'])->name('admins.login-histories');
Route::put('admins/{admin}/roles', [AdminController::class, 'assignRoles'])->name('admins.assign-roles');
Route::put('admins/{admin}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admins.toggle-status');
Route::put('admins/{admin}/reset-password', [AdminController::class, 'resetPassword'])->name('admins.reset-password');
Route::put('admins/change-password', [AdminController::class, 'changePassword'])->name('admins.change-password');
Route::apiResource('admins', AdminController::class);

// 用户管理
Route::get('users/{user}/login-histories', [UserController::class, 'loginHistories'])->name('users.login-histories');
Route::put('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
Route::put('users/{user}/reset-contact', [UserController::class, 'resetContact'])->name('users.reset-contact');
Route::put('users/{user}/adjust-balance', [UserController::class, 'adjustBalance'])->name('users.adjust-balance');
Route::put('users/{user}/extend-vip', [UserController::class, 'extendVip'])->name('users.extend-vip');
Route::apiResource('users', UserController::class)->except(['store']);

// 配置管理
Route::get('settings/groups', [SettingController::class, 'groups'])->name('settings.groups');
Route::put('settings/batch', [SettingController::class, 'batchUpdate'])->name('settings.batch');
Route::apiResource('settings', SettingController::class);

// 地区管理
Route::apiResource('areas', AreaController::class);

// 手机验证码管理（仅列表、详情，只读）
Route::get('phone-codes', [PhoneCodeController::class, 'index'])->name('phone-codes.index');
Route::get('phone-codes/{id}', [PhoneCodeController::class, 'show'])->name('phone-codes.show');

// 邮件验证码管理（仅列表、详情，只读）
Route::get('mail-codes', [MailCodeController::class, 'index'])->name('mail-codes.index');
Route::get('mail-codes/{id}', [MailCodeController::class, 'show'])->name('mail-codes.show');

// 上传管理
Route::post('uploader/file', [UploaderController::class, 'file'])->name('uploader.file');
Route::post('uploader/image', [UploaderController::class, 'image'])->name('uploader.image');
Route::post('uploader/video', [UploaderController::class, 'video'])->name('uploader.video');
Route::post('uploader/token', [UploaderController::class, 'uploadToken'])->name('uploader.token');

// AI 聊天
Route::get('chat/conversations', [ChatController::class, 'conversations'])->name('chat.conversations');
Route::get('chat/conversations/{conversationId}', [ChatController::class, 'conversation'])->name('chat.conversation');
Route::post('chat', [ChatController::class, 'stream'])->name('chat.send');
Route::post('chat/approve', [ChatController::class, 'approve'])->name('chat.approve');
Route::delete('chat/conversations/{conversationId}', [ChatController::class, 'destroy'])->name('chat.destroy');

