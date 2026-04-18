<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\SmsServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Overtrue\EasySms\EasySms;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 短信服务提供者测试
 */
#[CoversClass(SmsServiceProvider::class)]
class SmsServiceProviderTest extends TestCase
{
    /**
     * 测试 register 方法是否正确注册了 EasySms 服务
     */
    #[Test]
    #[TestDox('测试 register 方法是否正确注册了 EasySms 服务')]
    public function test_register(): void
    {
        // 创建容器实例
        $app = new Container;

        // 模拟配置
        $config = [
            'sms' => [
                'default' => 'aliyun',
                'gateways' => [
                    'aliyun' => [
                        'access_key_id' => 'test_key',
                        'access_key_secret' => 'test_secret',
                        'sign_name' => 'Test',
                    ],
                ],
            ],
        ];

        // 绑定配置到容器
        $app->instance('config', new Repository($config));

        // 创建服务提供者实例
        $provider = new SmsServiceProvider($app);

        // 调用 register 方法
        $provider->register();

        // 检查服务是否已注册
        $this->assertTrue($app->bound(EasySms::class));

        // 检查服务是否是单例
        $instance1 = $app->make(EasySms::class);
        $instance2 = $app->make(EasySms::class);
        $this->assertSame($instance1, $instance2);

        // 检查服务是否是 EasySms 实例
        $this->assertInstanceOf(EasySms::class, $instance1);
    }

    /**
     * 测试 boot 方法
     */
    #[Test]
    #[TestDox('测试 boot 方法')]
    public function test_boot(): void
    {
        // 创建容器实例
        $app = new Container;

        // 创建服务提供者实例
        $provider = new SmsServiceProvider($app);

        // 调用 boot 方法
        $result = $provider->boot();

        // 检查 boot 方法是否返回 null
        $this->assertNull($result);
    }
}
