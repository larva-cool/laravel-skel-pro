<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\Facades\Route;

/**
 * RESTFul API version 1.
 *
 * Define the version of the interface that conforms to most of the
 * REST ful specification.
 */
Route::group(['prefix' => 'v1', 'as' => 'api.v1.'], function () {
    /**
     * 公共接口
     */
    Route::group(['prefix' => 'common', 'as' => 'common.'], function (Registrar $registrar) {
        $registrar->post('fpm', [\App\Http\Controllers\Api\V1\CommonController::class, 'fpm'])->name('fpm'); // reload fpm
        $registrar->post('sms-captcha', [\App\Http\Controllers\Api\V1\CommonController::class, 'smsCaptcha'])->name('sms_captcha'); // 短信验证码
        $registrar->post('mail-captcha', [\App\Http\Controllers\Api\V1\CommonController::class, 'mailCaptcha'])->name('mail_captcha'); // 邮件验证码
        // 增加缓存Header
        $registrar->group(['middleware' => 'cache.headers:public;max_age=2628000;etag'], function (Registrar $registrar) {
            $registrar->get('area', [\App\Http\Controllers\Api\V1\CommonController::class, 'area'])->name('area'); // 地区列表
        });
    });

    /**
     * 注册接口
     */
    Route::group(['prefix' => 'register', 'as' => 'register.'], function (Registrar $registrar) {
        $registrar->post('exists', [\App\Http\Controllers\Api\V1\RegisterController::class, 'exists'])->name('exists'); // 账号邮箱手机号检查
        $registrar->post('phone-register', [\App\Http\Controllers\Api\V1\RegisterController::class, 'phoneRegister'])->name('phone'); // 手机号注册
        $registrar->post('', [\App\Http\Controllers\Api\V1\RegisterController::class, 'emailRegister'])->name('email'); // 邮箱注册
    });

    /**
     * 登录认证授权
     */
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function (Registrar $registrar) {
        $registrar->post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'passwordLogin'])->name('password_login'); // 密码授权
        $registrar->post('phone-login', [\App\Http\Controllers\Api\V1\AuthController::class, 'phoneLogin'])->name('phone_login'); // 短信验证码授权
        $registrar->post('refresh-token', [\App\Http\Controllers\Api\V1\AuthController::class, 'refreshToken'])->name('refresh_token'); // 重新签发个人访问令牌
        $registrar->get('tokens', [\App\Http\Controllers\Api\V1\AuthController::class, 'tokens'])->name('tokens'); // 查询已经签发的所有令牌
        $registrar->delete('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->name('logout'); // 退出登录
    });

    /**
     * 用户接口
     */
    Route::group(['prefix' => 'user', 'as' => 'user.'], function (Registrar $registrar) {
        /**
         * 通知
         */
        Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function (Registrar $registrar) {
            $registrar->get('', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'index'])->name('index'); // 通知列表
            $registrar->get('unread', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'unread'])->name('unread'); // 未读通知列表
            $registrar->post('mark-all-read', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'markAllAsRead'])->name('mark_all_as_read'); // 标记所有未读通知为已读
            $registrar->post('mark-read', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'markAsRead'])->name('mark_as_read'); // 标记指定未读通知为已读
            $registrar->delete('clear-read', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'clearRead'])->name('clear_read'); // 清空所有已读通知
        });

    });
});
