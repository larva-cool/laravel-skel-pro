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

// 角色管理
Route::get('roles/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'allPermissions'])->name('roles.permissions');
Route::get('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'permissions'])->name('roles.get-permissions');
Route::put('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');
Route::apiResource('roles', \App\Http\Controllers\Admin\RoleController::class);

// 前端路由配置
Route::get('routes', [\App\Http\Controllers\Admin\MainController::class, 'routes'])->name('routes');

// 通知管理
Route::get('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
Route::get('notifications/unread', [\App\Http\Controllers\Admin\NotificationController::class, 'unread'])->name('notifications.unread');
Route::put('notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
Route::put('notifications/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
Route::delete('notifications/clear-read', [\App\Http\Controllers\Admin\NotificationController::class, 'clearRead'])->name('notifications.clear-read');

// 菜单管理
Route::apiResource('menus', \App\Http\Controllers\Admin\MenuController::class);

// 管理员管理
Route::get('admins/profile', [\App\Http\Controllers\Admin\AdminController::class, 'profile'])->name('admins.profile');
Route::put('admins/profile', [\App\Http\Controllers\Admin\AdminController::class, 'updateProfile'])->name('admins.update-profile');
Route::post('admins/avatar', [\App\Http\Controllers\Admin\AdminController::class, 'updateAvatar'])->name('admins.update-avatar');
Route::get('admins/{admin}/roles', [\App\Http\Controllers\Admin\AdminController::class, 'roles'])->name('admins.roles');
Route::get('admins/{admin}/login-histories', [\App\Http\Controllers\Admin\AdminController::class, 'loginHistories'])->name('admins.login-histories');
Route::put('admins/{admin}/roles', [\App\Http\Controllers\Admin\AdminController::class, 'assignRoles'])->name('admins.assign-roles');
Route::put('admins/{admin}/toggle-status', [\App\Http\Controllers\Admin\AdminController::class, 'toggleStatus'])->name('admins.toggle-status');
Route::put('admins/{admin}/reset-password', [\App\Http\Controllers\Admin\AdminController::class, 'resetPassword'])->name('admins.reset-password');
Route::put('admins/change-password', [\App\Http\Controllers\Admin\AdminController::class, 'changePassword'])->name('admins.change-password');
Route::apiResource('admins', \App\Http\Controllers\Admin\AdminController::class);

// 用户管理
Route::get('users/{user}/login-histories', [\App\Http\Controllers\Admin\UserController::class, 'loginHistories'])->name('users.login-histories');
Route::get('users/{user}/socials', [\App\Http\Controllers\Admin\UserController::class, 'socials'])->name('users.socials');
Route::get('users/{user}/point-trades', [\App\Http\Controllers\Admin\UserController::class, 'pointTrades'])->name('users.point-trades');
Route::get('users/{user}/coin-trades', [\App\Http\Controllers\Admin\UserController::class, 'coinTrades'])->name('users.coin-trades');
Route::put('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
Route::put('users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
Route::put('users/{user}/reset-contact', [\App\Http\Controllers\Admin\UserController::class, 'resetContact'])->name('users.reset-contact');
Route::put('users/{user}/adjust-balance', [\App\Http\Controllers\Admin\UserController::class, 'adjustBalance'])->name('users.adjust-balance');
Route::put('users/{user}/extend-vip', [\App\Http\Controllers\Admin\UserController::class, 'extendVip'])->name('users.extend-vip');
Route::apiResource('users', \App\Http\Controllers\Admin\UserController::class)->except(['store']);

// 配置管理
Route::get('settings/groups', [\App\Http\Controllers\Admin\SettingController::class, 'groups'])->name('settings.groups');
Route::put('settings/batch', [\App\Http\Controllers\Admin\SettingController::class, 'batchUpdate'])->name('settings.batch');
Route::apiResource('settings', \App\Http\Controllers\Admin\SettingController::class);

// 地区管理
Route::apiResource('areas', \App\Http\Controllers\Admin\AreaController::class);

// 手机验证码管理（仅列表、详情，只读）
Route::get('phone-codes', [\App\Http\Controllers\Admin\PhoneCodeController::class, 'index'])->name('phone-codes.index');
Route::get('phone-codes/{id}', [\App\Http\Controllers\Admin\PhoneCodeController::class, 'show'])->name('phone-codes.show');

// 邮件验证码管理（仅列表、详情，只读）
Route::get('mail-codes', [\App\Http\Controllers\Admin\MailCodeController::class, 'index'])->name('mail-codes.index');
Route::get('mail-codes/{id}', [\App\Http\Controllers\Admin\MailCodeController::class, 'show'])->name('mail-codes.show');

// 上传管理
Route::post('uploader/file', [\App\Http\Controllers\Admin\UploaderController::class, 'file'])->name('uploader.file');
Route::post('uploader/image', [\App\Http\Controllers\Admin\UploaderController::class, 'image'])->name('uploader.image');
Route::post('uploader/video', [\App\Http\Controllers\Admin\UploaderController::class, 'video'])->name('uploader.video');
Route::post('uploader/token', [\App\Http\Controllers\Admin\UploaderController::class, 'uploadToken'])->name('uploader.token');

// 附件管理
Route::post('attachments/register', [\App\Http\Controllers\Admin\AttachmentController::class, 'register'])->name('attachments.register');
Route::delete('attachments', [\App\Http\Controllers\Admin\AttachmentController::class, 'batchDestroy'])->name('attachments.batch-destroy');
Route::get('attachments/{id}/download', [\App\Http\Controllers\Admin\AttachmentController::class, 'download'])->name('attachments.download');
Route::get('attachments/{id}/temporary-url', [\App\Http\Controllers\Admin\AttachmentController::class, 'temporaryUrl'])->name('attachments.temporary-url');
Route::put('attachments/{id}/rename', [\App\Http\Controllers\Admin\AttachmentController::class, 'rename'])->name('attachments.rename');
Route::put('attachments/{id}/move', [\App\Http\Controllers\Admin\AttachmentController::class, 'move'])->name('attachments.move');
Route::get('attachments', [\App\Http\Controllers\Admin\AttachmentController::class, 'index'])->name('attachments.index');
Route::get('attachments/{id}', [\App\Http\Controllers\Admin\AttachmentController::class, 'show'])->name('attachments.show');
Route::delete('attachments/{id}', [\App\Http\Controllers\Admin\AttachmentController::class, 'destroy'])->name('attachments.destroy');

// 性能监控（Laravel Pulse 数据）
Route::group(['prefix' => 'monitor'], function (Registrar $registrar) {
    $registrar->get('servers', [\App\Http\Controllers\Admin\MonitorController::class, 'servers'])->name('monitor.servers');
    $registrar->get('queues', [\App\Http\Controllers\Admin\MonitorController::class, 'queues'])->name('monitor.queues');
    $registrar->get('reverb/connections', [\App\Http\Controllers\Admin\MonitorController::class, 'reverbConnections'])->name('monitor.reverb.connections');
    $registrar->get('reverb/messages', [\App\Http\Controllers\Admin\MonitorController::class, 'reverbMessages'])->name('monitor.reverb.messages');
    $registrar->get('cache', [\App\Http\Controllers\Admin\MonitorController::class, 'cache'])->name('monitor.cache');
    $registrar->get('exceptions', [\App\Http\Controllers\Admin\MonitorController::class, 'exceptions'])->name('monitor.exceptions');
    $registrar->get('slow-queries', [\App\Http\Controllers\Admin\MonitorController::class, 'slowQueries'])->name('monitor.slow-queries');
    $registrar->get('slow-requests', [\App\Http\Controllers\Admin\MonitorController::class, 'slowRequests'])->name('monitor.slow-requests');
    $registrar->get('slow-jobs', [\App\Http\Controllers\Admin\MonitorController::class, 'slowJobs'])->name('monitor.slow-jobs');
    $registrar->get('slow-outgoing-requests', [\App\Http\Controllers\Admin\MonitorController::class, 'slowOutgoingRequests'])->name('monitor.slow-outgoing-requests');
    $registrar->get('usage', [\App\Http\Controllers\Admin\MonitorController::class, 'usage'])->name('monitor.usage');
});

// 调试面板（Laravel Telescope 数据）
Route::group(['prefix' => 'debug'], function (Registrar $registrar) {
    $registrar->get('tags', [\App\Http\Controllers\Admin\DebugController::class, 'tags'])->name('debug.tags');
    $registrar->post('tags', [\App\Http\Controllers\Admin\DebugController::class, 'monitor'])->name('debug.monitor');
    $registrar->delete('tags', [\App\Http\Controllers\Admin\DebugController::class, 'unmonitor'])->name('debug.unmonitor');
    $registrar->post('toggle-recording', [\App\Http\Controllers\Admin\DebugController::class, 'toggleRecording'])->name('debug.toggle-recording');
    $registrar->delete('entries', [\App\Http\Controllers\Admin\DebugController::class, 'destroy'])->name('debug.destroy');
    $registrar->get('entries', [\App\Http\Controllers\Admin\DebugController::class, 'index'])->name('debug.index');
    $registrar->get('entries/{id}', [\App\Http\Controllers\Admin\DebugController::class, 'show'])->name('debug.show');
    $registrar->put('entries/{id}/resolve', [\App\Http\Controllers\Admin\DebugController::class, 'resolve'])->name('debug.resolve');
});

// AI 聊天
Route::get('chat/conversations', [\App\Http\Controllers\Admin\ChatController::class, 'conversations'])->name('chat.conversations');
Route::get('chat/conversations/{conversationId}', [\App\Http\Controllers\Admin\ChatController::class, 'conversation'])->name('chat.conversation');
Route::post('chat', [\App\Http\Controllers\Admin\ChatController::class, 'stream'])->name('chat.send');
Route::post('chat/approve', [\App\Http\Controllers\Admin\ChatController::class, 'approve'])->name('chat.approve');
Route::delete('chat/conversations/{conversationId}', [\App\Http\Controllers\Admin\ChatController::class, 'destroy'])->name('chat.destroy');
