<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\CommonController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\RegisterController;
use App\Http\Controllers\Api\V1\SignInController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UploaderController;
use App\Http\Controllers\Api\V1\User\AddressController;
use App\Http\Controllers\Api\V1\User\AnnouncementController;
use App\Http\Controllers\Api\V1\User\NotificationController;
use App\Http\Controllers\Api\V1\UserController;
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
        $registrar->any('fpm', [CommonController::class, 'fpm'])->name('fpm'); // reload fpm
        $registrar->post('sms-captcha', [CommonController::class, 'smsCaptcha'])->name('sms_captcha'); // 短信验证码
        $registrar->post('mail-captcha', [CommonController::class, 'mailCaptcha'])->name('mail_captcha'); // 邮件验证码
        $registrar->get('nickname', [CommonController::class, 'nickname'])->name('nickname'); // 获取一个随机昵称
        // 增加缓存Header
        $registrar->group(['middleware' => 'cache.headers:public;max_age=2628000;etag'], function (Registrar $registrar) {
            $registrar->get('dict', [CommonController::class, 'dict'])->name('dict'); // 字典列表
            $registrar->get('area', [CommonController::class, 'area'])->name('area'); // 地区列表
            $registrar->get('source-types', [CommonController::class, 'sourceTypes'])->name('source_types'); // 获取 Source Types
            $registrar->get('settings', [CommonController::class, 'settings'])->name('settings'); // 系统配置
            $registrar->get('agreement/{type}', [CommonController::class, 'agreement'])->name('agreement'); // 协议详情
        });
    });

    /**
     * 注册接口
     */
    Route::group(['prefix' => 'register', 'as' => 'register.'], function (Registrar $registrar) {
        $registrar->post('exists', [RegisterController::class, 'exists'])->name('exists'); // 账号邮箱手机号检查
        $registrar->post('phone-register', [RegisterController::class, 'phoneRegister'])->name('phone'); // 手机号注册
        $registrar->post('', [RegisterController::class, 'emailRegister'])->name('email'); // 邮箱注册
    });

    /**
     * 登录认证授权
     */
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function (Registrar $registrar) {
        $registrar->post('login', [AuthController::class, 'passwordLogin'])->name('password_login'); // 密码授权
        $registrar->post('phone-login', [AuthController::class, 'phoneLogin'])->name('phone_login'); // 短信验证码授权
        $registrar->post('wx-login', [AuthController::class, 'wxLogin'])->name('wx_login'); // 微信公众号授权登录
        $registrar->post('apple-login', [AuthController::class, 'appleLogin'])->name('apple_login'); // Apple 登录授权
        $registrar->post('refresh-token', [AuthController::class, 'refreshToken'])->name('refresh_token'); // 重新签发个人访问令牌
        $registrar->get('tokens', [AuthController::class, 'tokens'])->name('tokens'); // 查询已经签发的所有令牌
        $registrar->delete('tokens/{tokenId}', [AuthController::class, 'destroyToken'])->name('destroy_token'); // 销毁指定的 Token
        $registrar->delete('tokens', [AuthController::class, 'destroyCurrentAccessToken'])->name('destroy_current_token'); // 销毁当前正在使用的 Token
        $registrar->post('phone-reset-password', [AuthController::class, 'resetPasswordByPhone'])->name('reset_password_by_phone'); // 通过手机重置用户登录密码
    });

    /**
     * 用户接口
     */
    Route::group(['prefix' => 'user', 'as' => 'user.'], function (Registrar $registrar) {
        /**
         * 通知
         */
        Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function (Registrar $registrar) {
            $registrar->get('', [NotificationController::class, 'index'])->name('index'); // 通知列表
            $registrar->get('unread', [NotificationController::class, 'unread'])->name('unread'); // 未读通知列表
            $registrar->post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark_all_as_read'); // 标记所有未读通知为已读
            $registrar->post('mark-read', [NotificationController::class, 'markAsRead'])->name('mark_as_read'); // 标记指定未读通知为已读
            $registrar->delete('clear-read', [NotificationController::class, 'clearRead'])->name('clear_read'); // 清空所有已读通知
        });

        /**
         * 公告
         */
        Route::group(['prefix' => 'announcement', 'as' => 'announcement.'], function (Registrar $registrar) {
            $registrar->get('', [AnnouncementController::class, 'index'])->name('index'); // 获取公告列表
            $registrar->get('{announcement}', [AnnouncementController::class, 'show'])->name('show');
        });

        $registrar->get('', [UserController::class, 'baseProfile'])->name('profile'); // 获取基本信息
        $registrar->post('verify-phone', [UserController::class, 'verifyPhone'])->name('verify_phone'); // 验证手机号码
        $registrar->post('profile', [UserController::class, 'modifyProfile'])->name('modify_profile'); // 修改个人资料
        $registrar->post('username', [UserController::class, 'modifyUsername'])->name('modify_username'); // 修改账号
        $registrar->post('email', [UserController::class, 'modifyEMail'])->name('modify_email'); // 修改邮箱
        $registrar->post('phone', [UserController::class, 'modifyPhone'])->name('modify_phone'); // 修改手机号码
        $registrar->post('avatar', [UserController::class, 'modifyAvatar'])->name('modify_avatar'); // 修改头像
        $registrar->post('password', [UserController::class, 'modifyPassword'])->name('modify_password'); // 修改密码
        $registrar->post('pay-password', [UserController::class, 'modifyPayPassword'])->name('modify_pay_password'); // 修改支付密码
        $registrar->post('socket-id', [UserController::class, 'modifySocketId'])->name('modify_socket_id'); // 修改 SocketID
        $registrar->get('login-histories', [UserController::class, 'loginHistories'])->name('login_histories'); // 获取登录历史
        $registrar->get('invites', [UserController::class, 'invites'])->name('invites'); // 获取邀请列表
        $registrar->get('points', [UserController::class, 'points'])->name('points'); // 获取用户积分记录
        $registrar->get('coins', [UserController::class, 'coins'])->name('coins'); // 获取用户金币记录
        $registrar->put('address/{address}/default', [AddressController::class, 'setDefault']); // 收货地址设为默认
        $registrar->apiResource('address', AddressController::class); // 收货地址
        $registrar->delete('', [UserController::class, 'destroy'])->name('destroy'); // 注销并删除自己的账户
    });

    /**
     * 上传接口
     */
    Route::group(['prefix' => 'uploader', 'as' => 'uploader.'], function (Registrar $registrar) {
        $registrar->post('image', [UploaderController::class, 'image'])->name('upload_image'); // 上传图片
    });

    /**
     * 签到
     */
    Route::group(['prefix' => 'sign-in', 'as' => 'sign-in.'], function (Registrar $registrar) {
        $registrar->get('', [SignInController::class, 'info'])->name('info'); // 获取签到信息
        $registrar->post('', [SignInController::class, 'sign'])->name('sign'); // 签到
    });

    // 任务中心（福利中心）
    Route::group(['prefix' => 'tasks', 'as' => 'tasks.'], function (Registrar $registrar) {
        $registrar->get('', [TaskController::class, 'index'])->name('index'); // 任务列表
        $registrar->post('{task}/claim', [TaskController::class, 'claim'])->name('claim'); // 领取任务奖励
    });

    /**
     * 评论
     */
    Route::group(['as' => 'comments.'], function (Registrar $registrar) {
        $registrar->get('{sourceType}/{sourceId}/comments', [CommentController::class, 'index'])->name('index');
        $registrar->post('comments', [CommentController::class, 'store'])->name('store');
        $registrar->delete('comments/{comment}', [CommentController::class, 'destroy'])->name('destroy');
    });

    /**
     * 收藏
     */
    Route::group(['as' => 'collections.'], function (Registrar $registrar) {
        $registrar->get('collections', [CollectionController::class, 'index'])->name('index');
        $registrar->post('collections', [CollectionController::class, 'store'])->name('store');
        $registrar->delete('collections/{collection}', [CollectionController::class, 'destroy'])->name('destroy');
    });

    /**
     * 点赞
     */
    Route::group(['as' => 'likes.'], function (Registrar $registrar) {
        $registrar->get('likes', [LikeController::class, 'index'])->name('index');
        $registrar->post('likes', [LikeController::class, 'store'])->name('store');
        $registrar->delete('likes/{like}', [LikeController::class, 'destroy'])->name('destroy');
    });

    /**
     * 反馈
     */
    Route::group(['prefix' => 'feedbacks', 'as' => 'feedbacks.'], function (Registrar $registrar) {
        $registrar->get('', [FeedbackController::class, 'index'])->name('index');
        $registrar->post('', [FeedbackController::class, 'store'])->name('store');
        $registrar->get('{feedback}', [FeedbackController::class, 'show'])->name('show');
    });
});
