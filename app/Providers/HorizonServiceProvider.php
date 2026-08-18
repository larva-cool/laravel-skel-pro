<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

/**
 * Horizon 队列监控服务提供者
 *
 * 认证分流由 App\Http\Middleware\HorizonAuthenticate 中间件完成：
 * - Dashboard 页面：web guard (Session) 认证
 * - API 接口：admin guard (Sanctum Bearer Token) 认证
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * 统一的访问授权判断：无论是 Admin（通过 Sanctum Token 认证）还是
     * 普通 User（通过 Web Session 认证），都走同一套权限检查逻辑。
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            if ($user === null) {
                return false;
            }

            // 方式一：邮箱白名单
            $allowedEmails = [
                // 'admin@example.com',
            ];
            if (in_array($user->email ?? '', $allowedEmails, true)) {
                return true;
            }

            // 方式二：Admin 模型通过 Spatie Permission 判断角色/权限
            if (method_exists($user, 'hasRole')) {
                return $user->hasRole('super-admin') || $user->can('horizon:view');
            }

            return false;
        });
    }
}
