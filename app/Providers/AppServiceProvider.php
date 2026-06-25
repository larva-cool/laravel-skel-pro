<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Providers;

use App\Models\Admin\Admin;
use App\Models\PersonalAccessToken;
use App\Services\SettingManagerService;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

/**
 * 应用服务
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 注册系统设置服务
        $this->app->singleton(SettingManagerService::class, function () {
            return new SettingManagerService;
        });
        // telescope 配置
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('zh');
        JsonResource::withoutWrapping();
        Model::shouldBeStrict(! $this->app->isProduction());
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Relation::enforceMorphMap(config('morph_maps'));

        // 修改登录 redirect
        Authenticate::redirectUsing('admin_login_url');

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            if ($user instanceof Admin) {
                return $user->hasRole('Super Admin') ? true : null;
            }

            return null;
        });

        // 定义 API 速率限制器
        RateLimiter::for('api', function (object $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60);
        });
    }
}
