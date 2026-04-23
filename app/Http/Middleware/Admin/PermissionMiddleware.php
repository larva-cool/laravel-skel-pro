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
use Illuminate\Support\Str;

/**
 * 权限检查
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PermissionMiddleware
{
    protected string $middlewarePrefix = 'admin.permission:';

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

        if (! $user || ! empty($args) || ! config('admin.permission.enable')
            || $this->shouldPassThrough($request)
            || $user->isAdministrator()
            || $this->checkRoutePermission($request)
        ) {
            return $next($request);
        }

        if (! $user->allPermissions()->first(function ($permission) use ($request) {
            return $permission->shouldPassThrough($request);
        })) {
            Checker::error();
        }

        return $next($request);
    }

    /**
     * If the route of current request contains a middleware prefixed with 'admin.permission:',
     * then it has a manually set permission middleware, we need to handle it first.
     *
     * @return bool
     */
    public function checkRoutePermission(Request $request)
    {
        if (! $middleware = collect($request->route()->middleware())->first(function ($middleware) {
            return Str::startsWith($middleware, $this->middlewarePrefix);
        })) {
            return false;
        }

        $args = explode(',', str_replace($this->middlewarePrefix, '', $middleware));

        $method = array_shift($args);

        if (! method_exists(Checker::class, $method)) {
            throw new \RuntimeException("Invalid permission method [$method].");
        }

        call_user_func_array([Checker::class, $method], [$args]);

        return true;
    }

    /**
     * @param  Request  $request
     */
    protected function isApiRoute($request): bool
    {
        return true;

        return $request->routeIs(admin_api_route_name('*'));
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param  Request  $request
     * @return bool
     */
    public function shouldPassThrough($request)
    {
        return true;
        if (Authenticate::shouldPassThrough($request)) {
            return true;
        }

        $excepts = (array) config('admin.permission.except', []);

        foreach ($excepts as $except) {
            if ($request->routeIs($except) || $request->routeIs(AdminHelper::getRouteName($except))) {
                return true;
            }

            $except = admin_base_path($except);

            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if (AdminHelper::matchRequestPath($except)) {
                return true;
            }
        }

        return false;
    }
}
