<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Events\Admin\LoginSucceeded;
use App\Http\Requests\Admin\Auth\PasswordLoginRequest;
use App\Http\Resources\Admin\AdminInfoResource;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

/**
 * 后台认证控制器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AuthController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin')->except(['login']);
    }

    /**
     * 获取当前管理员信息
     */
    public function info(Request $request): AdminInfoResource
    {
        /** @var Admin $admin */
        $admin = $request->user();

        return new AdminInfoResource($admin);
    }

    /**
     * 管理员登录
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(PasswordLoginRequest $request): JsonResponse
    {
        $admin = $request->authenticate();
        $token = $admin->createToken('admin-token', ['admin']);
        Event::dispatch(new Login(AdminMenu::GUARD_NAME, $admin, false));
        Event::dispatch(new LoginSucceeded($admin, $request->ip(), (string) $request->server('REMOTE_PORT'), $request->userAgent()));

        return response()->json([
            'access_token' => $token->plainTextToken,
            'user' => new AdminInfoResource($admin),
        ]);
    }

    /**
     * 管理员退出登录
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(status: 204);
    }
}
