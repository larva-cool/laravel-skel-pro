<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Events\User\InviteRegistered;
use App\Events\User\LoginSucceeded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Register\CheckAccountRequest;
use App\Http\Requests\Api\V1\Register\MailRegisterRequest;
use App\Http\Requests\Api\V1\Register\PhoneRegisterRequest;
use App\Models\User;
use App\Support\UserHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;

/**
 * 注册接口
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class RegisterController extends Controller
{
    /**
     * @var string 用户守卫
     */
    protected string $guard = 'sanctum';

    /**
     * Constructor.
     */
    public function __construct()
    {
        // 注册限速
        $throttle = 'throttle:'.settings('user.register_throttle', '6,1');
        $this->middleware($throttle)->only(['phoneRegister', 'emailRegister']);
    }

    /**
     * 用户账户是否存在
     */
    public function exists(CheckAccountRequest $request): JsonResponse
    {
        $query = User::withTrashed();
        if (! empty($request->email)) {
            $query->whereNotNull('email')->where('email', $request->email);
        } elseif (! empty($request->phone)) {
            $query->whereNotNull('phone')->where('phone', $request->phone);
        } else {
            $query->whereNotNull('username')->where('username', $request->username);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    /**
     * 邮箱注册接口
     *
     * @throws AuthorizationException
     */
    public function emailRegister(MailRegisterRequest $request)
    {
        $this->authorize('register', User::class);
        $this->authorize('emailRegister', User::class);
        if ($request->username) {
            $user = UserHelper::createByUsernameAndEmail($request->username, $request->email, $request->password);
        } else {
            $user = UserHelper::createByEmail($request->email, $request->password);
        }
        // 创建 Token
        $token = $user->createDeviceToken($request->device);
        Event::dispatch(new Registered($user));
        if ($request->invite_code) {
            Event::dispatch(new InviteRegistered($user, $request->invite_code));
        }
        Event::dispatch(new Login($this->guard, $user, false));
        Event::dispatch(new LoginSucceeded($user, $request->ip(), $request->server('REMOTE_PORT'),
            $request->userAgent()));

        return response()->json($token, 201);
    }

    /**
     * 手机注册接口
     *
     * @throws AuthorizationException
     */
    public function phoneRegister(PhoneRegisterRequest $request)
    {
        $this->authorize('register', User::class);
        $this->authorize('phoneRegister', User::class);
        $user = UserHelper::createByPhone($request->phone, $request->password);
        // 创建 Token
        $token = $user->createDeviceToken($request->device);
        Event::dispatch(new Registered($user));
        if ($request->invite_code) {
            Event::dispatch(new InviteRegistered($user, $request->invite_code));
        }
        Event::dispatch(new Login($this->guard, $user, false));
        Event::dispatch(new LoginSucceeded($user, $request->ip(), $request->server('REMOTE_PORT'),
            $request->userAgent()));

        return response()->json($token, 201);
    }
}
