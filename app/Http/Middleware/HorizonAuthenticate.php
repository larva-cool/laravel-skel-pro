<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Horizon 认证分流中间件
 *
 * 根据请求路径选择认证方式：
 * - /horizon/api/*  请求：强制使用 admin guard（Sanctum Bearer Token 认证），未携带 Token 返回 401
 * - Dashboard 页面请求：保持默认 web guard（Session 认证）
 *
 * 该中间件通过 config/horizon.php 的 middleware 数组注入，在 Horizon 自带
 * Authenticate 中间件之前执行，确保 Horizon::auth() 回调中 $request->user()
 * 能正确返回对应模型（Admin 或 User）。
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class HorizonAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('horizon/api/*')) {
            // API 请求：使用 admin guard (Sanctum) 认证
            Auth::shouldUse('admin');

            if (! $request->bearerToken()) {
                abort(401, 'Unauthenticated.');
            }

            if (! Auth::guard('admin')->check()) {
                abort(401, 'Unauthenticated.');
            }
        }

        return $next($request);
    }
}
