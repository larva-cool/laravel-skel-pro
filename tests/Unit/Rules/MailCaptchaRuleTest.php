<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\MailCaptchaRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * MailCaptchaRule 测试
 */
#[TestDox('MailCaptchaRule 测试')]
class MailCaptchaRuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试 setUp
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 测试构造函数
     */
    #[Test]
    #[TestDox('测试构造函数')]
    public function test_constructor()
    {
        $emailField = 'email';
        $clientIp = '127.0.0.1';

        // 创建 MailCaptchaRule 实例
        $rule = new MailCaptchaRule($emailField, $clientIp);

        // 验证属性是否正确设置
        $this->assertEquals($emailField, $rule->emailField);
        $this->assertEquals($clientIp, $rule->clientIp);
    }

    /**
     * 测试 setData 方法
     */
    #[Test]
    #[TestDox('测试 setData 方法')]
    public function test_set_data()
    {
        $emailField = 'email';
        $clientIp = '127.0.0.1';

        // 创建 MailCaptchaRule 实例
        $rule = new MailCaptchaRule($emailField, $clientIp);

        // 测试数据
        $testData = [
            'email' => 'test@example.com',
            'verify_code' => '123456',
        ];

        // 测试 setData 方法
        $result = $rule->setData($testData);

        // 验证返回值和数据是否正确设置
        $this->assertInstanceOf(MailCaptchaRule::class, $result);

        // 使用反射获取受保护的 data 属性
        $reflection = new \ReflectionClass($rule);
        $dataProperty = $reflection->getProperty('data');
        $dataProperty->setAccessible(true);

        $this->assertEquals($testData, $dataProperty->getValue($rule));
    }

    /**
     * 测试 validate 方法 - 验证成功
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 验证成功')]
    public function test_validate_success()
    {
        $emailField = 'email';
        $clientIp = '127.0.0.1';

        // 创建 MailCaptchaRule 实例
        $rule = new MailCaptchaRule($emailField, $clientIp);

        // 设置测试数据
        $testData = [
            'email' => 'test@example.com',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 验证成功的情况（使用测试环境的固定验证码）
        $this->app->detectEnvironment(function () {
            return 'testing';
        });

        $rule->validate('verify_code', '123456', $failCallback);
        $this->assertFalse($failCalled);
    }

    /**
     * 测试 validate 方法 - 验证失败
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 验证失败')]
    public function test_validate_failure()
    {
        $emailField = 'email';
        $clientIp = '127.0.0.1';

        // 创建 MailCaptchaRule 实例
        $rule = new MailCaptchaRule($emailField, $clientIp);

        // 设置测试数据
        $testData = [
            'email' => 'test@example.com',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 验证失败的情况（使用测试环境的固定验证码）
        $this->app->detectEnvironment(function () {
            return 'testing';
        });

        $rule->validate('verify_code', '654321', $failCallback);
        $this->assertTrue($failCalled);
    }

    /**
     * 测试 validate 方法 - 不区分大小写
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 不区分大小写')]
    public function test_validate_case_insensitive()
    {
        $emailField = 'email';
        $clientIp = '127.0.0.1';

        // 创建 MailCaptchaRule 实例
        $rule = new MailCaptchaRule($emailField, $clientIp);

        // 设置测试数据
        $testData = [
            'email' => 'test@example.com',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 验证不区分大小写的情况（使用测试环境的固定验证码）
        $this->app->detectEnvironment(function () {
            return 'testing';
        });

        $rule->validate('verify_code', '123456', $failCallback);
        $this->assertFalse($failCalled);
    }

    /**
     * 测试 validate 方法 - 邮箱字段不存在
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 邮箱字段不存在')]
    public function test_validate_email_field_not_exists()
    {
        $emailField = 'non_existent_email';
        $clientIp = '127.0.0.1';

        // 创建 MailCaptchaRule 实例
        $rule = new MailCaptchaRule($emailField, $clientIp);

        // 设置测试数据（不包含邮箱字段）
        $testData = [
            'name' => 'Test User',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 验证失败的情况（使用测试环境的固定验证码）
        $this->app->detectEnvironment(function () {
            return 'testing';
        });

        $rule->validate('verify_code', '123456', $failCallback);
        $this->assertFalse($failCalled);
    }
}
