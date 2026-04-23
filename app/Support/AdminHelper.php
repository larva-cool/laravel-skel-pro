<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

class AdminHelper
{
    /**
     * 匹配请求路径.
     *
     *@example
     *      Helper::matchRequestPath(admin_base_path('auth/user'))
     *      Helper::matchRequestPath(admin_base_path('auth/user*'))
     *      Helper::matchRequestPath(admin_base_path('auth/user/* /edit'))
     *      Helper::matchRequestPath('GET,POST:auth/user')
     */
    public static function matchRequestPath(string $path, ?string $current = null): bool
    {
        $request = request();
        $current = $current ?: $request->decodedPath();

        if (Str::contains($path, ':')) {
            [$methods, $path] = explode(':', $path);

            $methods = array_map('strtoupper', explode(',', $methods));

            if (! empty($methods) && ! in_array($request->method(), $methods)) {
                return false;
            }
        }

        // 判断路由名称
        if ($request->routeIs($path) || $request->routeIs(self::getRouteName($path))) {
            return true;
        }

        if (! Str::contains($path, '*')) {
            return $path === $current;
        }

        $path = str_replace(['*', '/'], ['([0-9a-z-_,])*', "\/"], $path);

        return preg_match("/$path/i", $current);
    }

    /**
     * 获取路由别名.
     */
    public static function getRouteName(?string $route): string
    {
        return config('admin.route.prefix').$route;
    }
}
