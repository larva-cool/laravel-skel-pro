<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\UsernameRule;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * UsernameRule 验证规则测试
 */
#[TestDox('UsernameRule 验证规则测试')]
class UsernameRuleTest extends TestCase
{
    /**
     * 测试有效用户名
     */
    #[Test]
    #[TestDox('测试有效用户名')]
    public function test_valid_usernames()
    {
        $rule = new UsernameRule;
        $failCalled = false;

        // 测试有效的用户名
        $validUsernames = [
            'john',
            'John',
            'john_doe',
            'john-doe',
            'john123',
            'John123',
            'j',
            '123',
            'user_name123',
            'user-name123',
        ];

        foreach ($validUsernames as $username) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $rule->validate('username', $username, $fail);
            $this->assertFalse($failCalled, "用户名 '$username' 应该是有效的");
        }
    }

    /**
     * 测试无效用户名
     */
    #[Test]
    #[TestDox('测试无效用户名')]
    public function test_invalid_usernames()
    {
        $rule = new UsernameRule;
        $failCalled = false;

        // 测试无效的用户名
        $invalidUsernames = [
            'john doe', // 包含空格
            'john@doe', // 包含 @ 符号
            'john.doe', // 包含点
            'john#doe', // 包含 # 符号
            'john$doe', // 包含 $ 符号
            'john%doe', // 包含 % 符号
            'john^doe', // 包含 ^ 符号
            'john&doe', // 包含 & 符号
            'john*doe', // 包含 * 符号
            'john(doe', // 包含 ( 符号
            'john)doe', // 包含 ) 符号
            'john+doe', // 包含 + 符号
            'john=doe', // 包含 = 符号
            'john[doe', // 包含 [ 符号
            'john]doe', // 包含 ] 符号
            'john{doe', // 包含 { 符号
            'john}doe', // 包含 } 符号
            'john|doe', // 包含 | 符号
            'john\\doe', // 包含 \ 符号
            'john:doe', // 包含 : 符号
            'john;doe', // 包含 ; 符号
            'john"doe', // 包含 " 符号
            'john\'doe', // 包含 ' 符号
            'john<doe', // 包含 < 符号
            'john>doe', // 包含 > 符号
            'john?doe', // 包含 ? 符号
            'john,doe', // 包含 , 符号
            'john.doee', // 包含点
            '张三', // 包含中文字符
        ];

        foreach ($invalidUsernames as $username) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $rule->validate('username', $username, $fail);
            $this->assertTrue($failCalled, "用户名 '$username' 应该是无效的");
        }
    }

    /**
     * 测试非标量输入
     */
    #[Test]
    #[TestDox('测试非标量输入')]
    public function test_non_scalar_inputs()
    {
        $rule = new UsernameRule;
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

            $rule->validate('username', $input, $fail);
            $this->assertTrue($failCalled, '非标量输入应该是无效的');
        }
    }
}
