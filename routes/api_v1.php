<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * RESTFul API version 1.
 *
 * Define the version of the interface that conforms to most of the
 * REST ful specification.
 */
Route::group(['prefix' => 'v1', 'as' => 'api.v1.'], function () {


    /**
     * 登录认证授权
     */
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function (Illuminate\Contracts\Routing\Registrar $registrar) {
        $registrar->post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'passwordLogin'])->name('password_login'); // 密码授权
        $registrar->post('phone-login', [\App\Http\Controllers\Api\V1\AuthController::class, 'phoneLogin'])->name('phone_login'); // 短信验证码授权
        $registrar->post('refresh-token', [\App\Http\Controllers\Api\V1\AuthController::class, 'refreshToken'])->name('refresh_token'); // 重新签发个人访问令牌
        $registrar->get('tokens', [\App\Http\Controllers\Api\V1\AuthController::class, 'tokens'])->name('tokens'); // 查询已经签发的所有令牌
        $registrar->delete('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->name('logout'); // 退出登录
    });

});
