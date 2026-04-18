<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Events\User\LoginSucceeded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\AppleLoginRequest;
use App\Http\Requests\Api\V1\Auth\PasswordLoginRequest;
use App\Http\Requests\Api\V1\Auth\PhoneLoginRequest;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordByPhoneRequest;
use App\Http\Requests\Api\V1\Auth\WechatMpLoginRequest;
use App\Http\Resources\Api\V1\TokenResource;
use App\Jobs\User\DeleteAccessTokenJob;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

/**
 * 认证授权 <第一方调用，直接用个人令牌>
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AuthController extends Controller
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
        $this->middleware('auth:sanctum')->except(['passwordLogin', 'phoneLogin', 'wxLogin', 'appleLogin', 'resetPasswordByPhone']);
        // 登录限速
        $throttle = 'throttle:'.settings('user.login_throttle', '6,1');
        $this->middleware($throttle)->only(['passwordLogin', 'phoneLogin', 'resetPasswordByPhone']);
    }

    /**
     * 密码登录
     *
     * @throws ValidationException|AuthorizationException
     */
    public function passwordLogin(PasswordLoginRequest $request): JsonResponse
    {
        $this->authorize('passwordLogin', User::class);
        $user = $request->authenticate();
        $token = $user->createDeviceToken($request->device);
        Event::dispatch(new Login($this->guard, $user, false));
        Event::dispatch(new LoginSucceeded($user, $request->ip(), $request->server('REMOTE_PORT'),
            $request->userAgent()));

        return response()->json($token);
    }

    /**
     * 手机登录
     */
    public function phoneLogin(PhoneLoginRequest $request): JsonResponse
    {
        $this->authorize('phoneLogin', User::class);
        $user = $request->authenticate();
        $token = $user->createDeviceToken($request->device);
        Event::dispatch(new Login($this->guard, $user, false));
        Event::dispatch(new LoginSucceeded($user, $request->ip(), $request->server('REMOTE_PORT'),
            $request->userAgent()));

        return response()->json($token);
    }

    /**
     * 微信公众号登录
     */
    public function wxLogin(WechatMpLoginRequest $request): JsonResponse
    {
        $this->authorize('wechatLogin', User::class);
        $user = $request->authenticate();
        $token = $user->createDeviceToken($request->device);
        Event::dispatch(new Login($this->guard, $user, false));
        Event::dispatch(new LoginSucceeded($user, $request->ip(), $request->server('REMOTE_PORT'),
            $request->userAgent()));

        return response()->json($token);
    }

    /**
     * 苹果账号登录
     */
    public function appleLogin(AppleLoginRequest $request): JsonResponse
    {
        $this->authorize('appleLogin', User::class);
        $user = $request->authenticate();
        $token = $user->createDeviceToken($request->device);
        Event::dispatch(new Login($this->guard, $user, false));
        Event::dispatch(new LoginSucceeded($user, $request->ip(), $request->server('REMOTE_PORT'),
            $request->userAgent()));

        return response()->json($token);
    }

    /**
     * 通过短信重置密码
     */
    public function resetPasswordByPhone(ResetPasswordByPhoneRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = User::query()->where('phone', $request->phone)->first();
        // 清理掉该用户所有的 Token
        $user->flushTokens();
        $user->resetPassword($request->password);

        return response()->json(['message' => __('user.password_reset_complete')]);
    }

    /**
     * 重新签发访问令牌
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $tokenModel = $request->user()->currentAccessToken();
        if ($request->user()->isFrozen()) {// 禁止掉的用户不允许登录
            $request->user()->flushTokens();
            validation_exception('code', __('auth.blocked'));
        }
        $token = $request->user()->createDeviceToken($request->device);
        // 一分钟后删除当前这个Token
        DeleteAccessTokenJob::dispatch($tokenModel->token)->delay(now()->addMinutes(1));
        Event::dispatch(new Login($this->guard, $request->user(), false));
        Event::dispatch(new LoginSucceeded($request->user(), $request->ip(), $request->server('REMOTE_PORT'),
            $request->userAgent()));

        return response()->json($token);
    }

    /**
     * 获取已经签发的所有 Token
     */
    public function tokens(Request $request): AnonymousResourceCollection
    {
        $perPage = clamp($request->query('per_page', 15), 1, 100);
        $items = $request->user()->tokens()->orderByDesc('id')->paginate($perPage);

        return TokenResource::collection($items);
    }

    /**
     * 销毁当前正在使用的 Token
     */
    public function destroyCurrentAccessToken(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /**
     * 撤销指定的 Token
     */
    public function destroyToken(Request $request, $tokenId): Response
    {
        $token = $request->user()->tokens()->where('id', $tokenId)->first();
        if (! $token) {
            return response()->noContent(404);
        }
        $token->delete();

        return response()->noContent();
    }
}
