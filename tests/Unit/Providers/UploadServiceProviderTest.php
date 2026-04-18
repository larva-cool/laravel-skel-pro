<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\UploadServiceProvider;
use App\Services\UploadService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * UploadServiceProvider 测试
 */
#[TestDox('UploadServiceProvider 测试')]
class UploadServiceProviderTest extends TestCase
{
    /**
     * 测试 register 方法
     */
    #[Test]
    #[TestDox('测试 register 方法')]
    public function test_register()
    {
        // 创建一个模拟的应用容器
        $app = $this->createMock('Illuminate\Foundation\Application');

        // 验证 singleton 方法是否被调用，并且参数正确
        $app->expects($this->once())
            ->method('singleton')
            ->with(
                UploadService::class,
                $this->isType('callable')
            );

        // 创建服务提供者实例并调用 register 方法
        $provider = new UploadServiceProvider($app);
        $provider->register();
    }

    /**
     * 测试 boot 方法
     */
    #[Test]
    #[TestDox('测试 boot 方法')]
    public function test_boot()
    {
        // 创建一个模拟的应用容器
        $app = $this->createMock('Illuminate\Foundation\Application');

        // 创建服务提供者实例并调用 boot 方法
        $provider = new UploadServiceProvider($app);
        $provider->boot();

        // 由于 boot 方法是空的，我们只需要确保它不会抛出异常
        $this->assertTrue(true);
    }
}
