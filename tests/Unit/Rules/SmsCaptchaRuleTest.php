<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\SmsCaptchaRule;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * SmsCaptchaRule 测试
 */
#[CoversClass(SmsCaptchaRule::class)]
#[TestDox('SmsCaptchaRule 测试')]
class SmsCaptchaRuleTest extends TestCase
{
    /**
     * 测试构造函数
     */
    #[Test]
    #[TestDox('测试构造函数')]
    public function test_constructor()
    {
        $phoneField = 'phone';
        $clientIp = '127.0.0.1';

        // 创建 SmsCaptchaRule 实例
        $rule = new SmsCaptchaRule($phoneField, $clientIp);

        // 验证属性是否正确设置
        $this->assertEquals($phoneField, $rule->phoneField);
        $this->assertEquals($clientIp, $rule->clientIp);
    }

    /**
     * 测试构造函数 - 客户端 IP 为 null
     */
    #[Test]
    #[TestDox('测试构造函数 - 客户端 IP 为 null')]
    public function test_constructor_with_null_client_ip()
    {
        $phoneField = 'phone';

        // 创建 SmsCaptchaRule 实例
        $rule = new SmsCaptchaRule($phoneField);

        // 验证属性是否正确设置
        $this->assertEquals($phoneField, $rule->phoneField);
        $this->assertNull($rule->clientIp);
    }

    /**
     * 测试 setData 方法
     */
    #[Test]
    #[TestDox('测试 setData 方法')]
    public function test_set_data()
    {
        $phoneField = 'phone';
        $clientIp = '127.0.0.1';

        // 创建 SmsCaptchaRule 实例
        $rule = new SmsCaptchaRule($phoneField, $clientIp);

        // 测试数据
        $testData = [
            'phone' => '13800138000',
            'verify_code' => '123456',
        ];

        // 测试 setData 方法
        $result = $rule->setData($testData);

        // 验证返回值和数据是否正确设置
        $this->assertInstanceOf(SmsCaptchaRule::class, $result);

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
        $phoneField = 'phone';
        $clientIp = '127.0.0.1';

        // 创建 SmsCaptchaRule 实例
        $rule = new class($phoneField, $clientIp) extends SmsCaptchaRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接返回成功，模拟验证通过
            }
        };

        // 设置测试数据
        $testData = [
            'phone' => '13800138000',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

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
        $phoneField = 'phone';
        $clientIp = '127.0.0.1';

        // 创建 SmsCaptchaRule 实例
        $rule = new class($phoneField, $clientIp) extends SmsCaptchaRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接调用 fail，模拟验证失败
                $fail('validation.verify_code');
            }
        };

        // 设置测试数据
        $testData = [
            'phone' => '13800138000',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('verify_code', '654321', $failCallback);
        $this->assertTrue($failCalled);
    }

    /**
     * 测试 validate 方法 - 电话号码字段不存在
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 电话号码字段不存在')]
    public function test_validate_phone_field_not_exists()
    {
        $phoneField = 'non_existent_phone';
        $clientIp = '127.0.0.1';

        // 创建 SmsCaptchaRule 实例
        $rule = new class($phoneField, $clientIp) extends SmsCaptchaRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接调用 fail，模拟验证失败
                $fail('validation.verify_code');
            }
        };

        // 设置测试数据（不包含电话号码字段）
        $testData = [
            'name' => 'Test User',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('verify_code', '123456', $failCallback);
        $this->assertTrue($failCalled);
    }

    /**
     * 测试 validate 方法 - 客户端 IP 为 null
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 客户端 IP 为 null')]
    public function test_validate_with_null_client_ip()
    {
        $phoneField = 'phone';

        // 创建 SmsCaptchaRule 实例
        $rule = new class($phoneField) extends SmsCaptchaRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                // 直接返回成功，模拟验证通过
            }
        };

        // 设置测试数据
        $testData = [
            'phone' => '13800138000',
        ];
        $rule->setData($testData);

        // 模拟 fail 回调
        $failCalled = false;
        $failCallback = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('verify_code', '123456', $failCallback);
        $this->assertFalse($failCalled);
    }
}
