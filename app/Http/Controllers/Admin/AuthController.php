<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Events\User\LoginSucceeded;
use App\Http\Requests\Admin\Auth\PasswordLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

/**
 * 管理员
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AuthController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin')->except(['showLoginForm', 'login']);
    }

    /**
     * 登录页
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * 登录验证
     */
    public function login(PasswordLoginRequest $request): JsonResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        Event::dispatch(new LoginSucceeded($request->user('admin'), $request->ip(), $request->server('REMOTE_PORT'), $request->userAgent()));

        return $this->success(__('user.login_success'));
    }

    /**
     * 退出登录
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->success(__('user.logout_successful'));
    }
}
