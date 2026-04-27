<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Middleware\Admin;

use App\Models\Admin\Admin;
use App\Support\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 权限检查
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  array  $args
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ...$args)
    {
        /** @var Admin|null $user */
        $user = Auth::guard('admin')->user();



        if (!$user || !empty($args) || !config('admin.permission.enable') || $this->shouldPassThrough($request) || $user->isAdministrator()) {
            return $next($request);
        }

        if (!$user->allPermissions()->first(function ($permission) use ($request) {
            return $permission->shouldPassThrough($request);
        })) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        return $next($request);
    }

    /**
     * 判断请求中的URI是否应通过验证。
     *
     * @param  Request  $request
     * @return bool
     */
    public function shouldPassThrough(Request $request): bool
    {
        // 检查是否是 API 请求
        if ($request->routeIs(AdminHelper::getRouteName('api.*'))) {
            return true;
        }

        // 检查是否忽略检查权限
        $excepts = (array) config('admin.permission.except', []);
        foreach ($excepts as $except) {
            if (AdminHelper::matchRequestPath($except, $request->decodedPath())) {
                return true;
            }
        }

        return false;
    }
}
