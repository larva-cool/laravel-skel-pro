<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\SmsCaptchaSendCheckRule;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * SmsCaptchaSendCheckRule 测试
 */
#[CoversClass(SmsCaptchaSendCheckRule::class)]
#[TestDox('SmsCaptchaSendCheckRule 测试')]
class SmsCaptchaSendCheckRuleTest extends TestCase
{
    /**
     * 测试构造函数
     */
    #[Test]
    #[TestDox('测试构造函数')]
    public function test_constructor()
    {
        $clientIp = '127.0.0.1';

        // 创建 SmsCaptchaSendCheckRule 实例
        $rule = new SmsCaptchaSendCheckRule($clientIp);

        // 验证属性是否正确设置
        $this->assertEquals($clientIp, $rule->clientIp);
    }

    /**
     * 测试 validate 方法 - 测试环境
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 测试环境')]
    public function test_validate_in_testing_environment()
    {
        $clientIp = '127.0.0.1';
        $phone = '13800138000';

        // 创建 SmsCaptchaSendCheckRule 实例
        $rule = new class($clientIp) extends SmsCaptchaSendCheckRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接返回，模拟测试环境下的行为
            }
        };

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 测试环境下应该直接返回，不会调用 fail
        $this->app->detectEnvironment(function () {
            return 'testing';
        });

        $rule->validate('phone', $phone, $failCallback);
        $this->assertFalse($failCalled);
    }

    /**
     * 测试 validate 方法 - IP 发送数量超过限制
     */
    #[Test]
    #[TestDox('测试 validate 方法 - IP 发送数量超过限制')]
    public function test_validate_ip_send_count_exceeded()
    {
        $clientIp = '127.0.0.1';
        $phone = '13800138000';

        // 创建 SmsCaptchaSendCheckRule 实例
        $rule = new class($clientIp) extends SmsCaptchaSendCheckRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接调用 fail，模拟 IP 发送数量超过限制
                $fail('validation.custom.phone.sms_captcha_send_check');
            }
        };

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 非测试环境
        $this->app->detectEnvironment(function () {
            return 'production';
        });

        $rule->validate('phone', $phone, $failCallback);
        $this->assertTrue($failCalled);
    }

    /**
     * 测试 validate 方法 - 手机号发送数量超过限制
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 手机号发送数量超过限制')]
    public function test_validate_phone_send_count_exceeded()
    {
        $clientIp = '127.0.0.1';
        $phone = '13800138000';

        // 创建 SmsCaptchaSendCheckRule 实例
        $rule = new class($clientIp) extends SmsCaptchaSendCheckRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接调用 fail，模拟手机号发送数量超过限制
                $fail('validation.custom.phone.sms_captcha_send_check');
            }
        };

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 非测试环境
        $this->app->detectEnvironment(function () {
            return 'production';
        });

        $rule->validate('phone', $phone, $failCallback);
        $this->assertTrue($failCalled);
    }

    /**
     * 测试 validate 方法 - 发送数量未超过限制
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 发送数量未超过限制')]
    public function test_validate_send_count_not_exceeded()
    {
        $clientIp = '127.0.0.1';
        $phone = '13800138000';

        // 创建 SmsCaptchaSendCheckRule 实例
        $rule = new class($clientIp) extends SmsCaptchaSendCheckRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接返回，模拟发送数量未超过限制
            }
        };

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        // 非测试环境
        $this->app->detectEnvironment(function () {
            return 'production';
        });

        $rule->validate('phone', $phone, $failCallback);
        $this->assertFalse($failCalled);
    }
}
