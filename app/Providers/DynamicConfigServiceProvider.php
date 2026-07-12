<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Providers;

use App\Services\SettingManagerService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * 动态配置服务提供器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class DynamicConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $instance = Container::getInstance()->make(SettingManagerService::class);
            // 上传配置
            if ($instance->has('upload.storage')) {
                Config::set('filesystems.default', $instance->get('upload.storage'));
            }
            // 短信配置<火山引擎>
            if ($instance->has('sms.region_id')) {
                Config::set('sms.gateways.region_id', $instance->get('sms.region_id'));
            }
            if ($instance->has('sms.sign_name')) {
                Config::set('sms.gateways.volcengine.sign_name', $instance->get('sms.sign_name'));
            }
            if ($instance->has('sms.sms_account')) {
                Config::set('sms.gateways.volcengine.sms_account', $instance->get('sms.sms_account'));
            }
            // 阿里云短信
            if ($instance->has('sms.aliyun_sign_name')) {
                Config::set('sms.gateways.aliyun.sign_name', $instance->get('sms.aliyun_sign_name'));
            }
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
        }
    }
}
