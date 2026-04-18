<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\PhoneRule;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * PhoneRule 验证规则测试
 */
#[TestDox('PhoneRule 验证规则测试')]
class PhoneRuleTest extends TestCase
{
    /**
     * 测试有效手机号码
     */
    #[Test]
    #[TestDox('测试有效手机号码')]
    public function test_valid_phone_numbers()
    {
        $rule = new PhoneRule;
        $failCalled = false;

        // 测试有效的手机号码
        $validPhones = [
            '13012345678',
            '13112345678',
            '13212345678',
            '13312345678',
            '13412345678',
            '13512345678',
            '13612345678',
            '13712345678',
            '13812345678',
            '13912345678',
            '14512345678',
            '14712345678',
            '15012345678',
            '15112345678',
            '15212345678',
            '15312345678',
            '15512345678',
            '15612345678',
            '15712345678',
            '15812345678',
            '15912345678',
            '16612345678',
            '17012345678',
            '17112345678',
            '17212345678',
            '17312345678',
            '17512345678',
            '17612345678',
            '17712345678',
            '17812345678',
            '18012345678',
            '18112345678',
            '18212345678',
            '18312345678',
            '18412345678',
            '18512345678',
            '18612345678',
            '18712345678',
            '18812345678',
            '18912345678',
            '19112345678',
            '19912345678',
        ];

        foreach ($validPhones as $phone) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $rule->validate('phone', $phone, $fail);
            $this->assertFalse($failCalled, "手机号码 '$phone' 应该是有效的");
        }
    }

    /**
     * 测试无效手机号码
     */
    #[Test]
    #[TestDox('测试无效手机号码')]
    public function test_invalid_phone_numbers()
    {
        $rule = new PhoneRule;
        $failCalled = false;

        // 测试无效的手机号码
        $invalidPhones = [
            '10012345678', // 第一位不是 1
            '11012345678', // 第二位不是 2-9
            '1201234567',  // 长度不足 11 位
            '120123456789', // 长度超过 11 位
            '1201234567a', // 包含非数字字符
            '22012345678', // 第一位不是 1
            '1201234567',  // 长度不足
        ];

        foreach ($invalidPhones as $phone) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $rule->validate('phone', $phone, $fail);
            $this->assertTrue($failCalled, "手机号码 '$phone' 应该是无效的");
        }
    }

    /**
     * 测试非标量输入
     */
    #[Test]
    #[TestDox('测试非标量输入')]
    public function test_non_scalar_inputs()
    {
        $rule = new PhoneRule;
        $failCalled = false;

        // 测试非标量输入
        $nonScalarInputs = [
            [],
            [],
            new \stdClass,
            null,
        ];

        foreach ($nonScalarInputs as $input) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $rule->validate('phone', $input, $fail);
            $this->assertTrue($failCalled, '非标量输入应该是无效的');
        }
    }
}
