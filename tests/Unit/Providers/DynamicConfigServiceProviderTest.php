<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\DynamicConfigServiceProvider;
use App\Services\SettingManagerService;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * DynamicConfigServiceProvider 测试
 */
#[TestDox('DynamicConfigServiceProvider 测试')]
class DynamicConfigServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试 boot 方法 - 正常流程
     */
    #[Test]
    #[TestDox('测试 boot 方法 - 正常流程')]
    public function test_boot_with_valid_config()
    {
        // 模拟 SettingManagerService
        $settingManagerMock = $this->createMock(SettingManagerService::class);
        $settingManagerMock->method('has')
            ->with('upload.storage')
            ->willReturn(true);
        $settingManagerMock->method('get')
            ->with('upload.storage')
            ->willReturn('s3');

        // 绑定模拟实例到容器
        Container::getInstance()->instance(SettingManagerService::class, $settingManagerMock);

        // 重置配置
        Config::set('filesystems.default', 'local');

        // 创建服务提供者实例并调用 boot 方法
        $provider = new DynamicConfigServiceProvider($this->app);
        $provider->boot();

        // 验证配置是否被正确设置
        $this->assertEquals('s3', Config::get('filesystems.default'));
    }

    /**
     * 测试 boot 方法 - 配置不存在
     */
    #[Test]
    #[TestDox('测试 boot 方法 - 配置不存在')]
    public function test_boot_without_config()
    {
        // 模拟 SettingManagerService
        $settingManagerMock = $this->createMock(SettingManagerService::class);
        $settingManagerMock->method('has')
            ->with('upload.storage')
            ->willReturn(false);

        // 绑定模拟实例到容器
        Container::getInstance()->instance(SettingManagerService::class, $settingManagerMock);

        // 设置初始配置
        Config::set('filesystems.default', 'local');

        // 创建服务提供者实例并调用 boot 方法
        $provider = new DynamicConfigServiceProvider($this->app);
        $provider->boot();

        // 验证配置是否保持不变
        $this->assertEquals('local', Config::get('filesystems.default'));
    }

    /**
     * 测试 boot 方法 - 异常处理
     */
    #[Test]
    #[TestDox('测试 boot 方法 - 异常处理')]
    public function test_boot_with_exception()
    {
        // 模拟 SettingManagerService
        $settingManagerMock = $this->createMock(SettingManagerService::class);
        $settingManagerMock->method('has')
            ->with('upload.storage')
            ->willThrowException(new \Exception('Test exception'));

        // 绑定模拟实例到容器
        Container::getInstance()->instance(SettingManagerService::class, $settingManagerMock);

        // 模拟 Log  facade
        Log::shouldReceive('warning')
            ->with('Test exception')
            ->once();

        // 设置初始配置
        Config::set('filesystems.default', 'local');

        // 创建服务提供者实例并调用 boot 方法
        $provider = new DynamicConfigServiceProvider($this->app);
        $provider->boot();

        // 验证配置是否保持不变
        $this->assertEquals('local', Config::get('filesystems.default'));
    }

    /**
     * 测试 register 方法
     */
    #[Test]
    #[TestDox('测试 register 方法')]
    public function test_register()
    {
        // 创建服务提供者实例并调用 register 方法
        $provider = new DynamicConfigServiceProvider($this->app);
        $provider->register();

        // 由于 register 方法是空的，我们只需要确保它不会抛出异常
        $this->assertTrue(true);
    }
}
