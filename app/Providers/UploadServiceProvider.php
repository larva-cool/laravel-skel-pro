<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Providers;

use App\Services\UploadService;
use Illuminate\Support\ServiceProvider;

/**
 * 文件服务提供器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UploadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 注册文件服务
        $this->app->singleton(UploadService::class, function () {
            return new UploadService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
