<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\NameRule;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * NameRule 验证规则测试
 */
#[TestDox('NameRule 验证规则测试')]
class NameRuleTest extends TestCase
{
    /**
     * 测试有效输入
     */
    #[Test]
    #[TestDox('测试有效输入')]
    public function test_valid_inputs()
    {
        $rule = new NameRule;
        $failCalled = false;

        // 测试有效的用户名
        $validInputs = [
            'John',
            'john_doe',
            'john.doe',
            'john@doe',
            'john-doe',
            'John123',
            '张三',
            '张三李四',
            '张三123',
        ];

        foreach ($validInputs as $input) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $rule->validate('name', $input, $fail);
            $this->assertFalse($failCalled, "输入 '$input' 应该是有效的");
        }
    }

    /**
     * 测试无效输入
     */
    #[Test]
    #[TestDox('测试无效输入')]
    public function test_invalid_inputs()
    {
        $rule = new NameRule;
        $failCalled = false;

        // 测试无效的用户名
        $invalidInputs = [
            'John@#$',
            'John Doe!',
            'john/doe',
            'john\doe',
            'john*doe',
            'john?doe',
            'john doe',
        ];

        foreach ($invalidInputs as $input) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $rule->validate('name', $input, $fail);
            $this->assertTrue($failCalled, "输入 '$input' 应该是无效的");
        }
    }

    /**
     * 测试非标量输入
     */
    #[Test]
    #[TestDox('测试非标量输入')]
    public function test_non_scalar_inputs()
    {
        $rule = new NameRule;
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

            $rule->validate('name', $input, $fail);
            $this->assertTrue($failCalled, '非标量输入应该是无效的');
        }
    }
}
