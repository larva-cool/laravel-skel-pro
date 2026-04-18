<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\CoinType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试金币交易类型枚举
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(CoinType::class)]
class CoinTypeTest extends TestCase
{
    /**
     * 测试 CoinType 枚举是否正确实现 JsonSerializable 接口
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举是否正确实现 JsonSerializable 接口')]
    public function test_coin_type_implements_json_serializable()
    {
        $coinType = CoinType::TYPE_SIGN_IN;

        $this->assertInstanceOf('JsonSerializable', $coinType);
    }

    /**
     * 测试 CoinType 枚举的所有值是否正确定义
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举的所有值是否正确定义')]
    public function test_coin_type_values_are_correctly_defined()
    {
        $this->assertTrue(defined('App\\Enum\\CoinType::TYPE_UNKNOWN'));
        $this->assertTrue(defined('App\\Enum\\CoinType::TYPE_SIGN_IN'));
        $this->assertTrue(defined('App\\Enum\\CoinType::TYPE_INVITE_REGISTER'));
    }

    /**
     * 测试 CoinType 枚举的 value 属性是否正确
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举的 value 属性是否正确')]
    public function test_coin_type_value_properties_are_correct()
    {
        $this->assertEquals('unknown_type', CoinType::TYPE_UNKNOWN->value);
        $this->assertEquals('sign_in', CoinType::TYPE_SIGN_IN->value);
        $this->assertEquals('invite_register', CoinType::TYPE_INVITE_REGISTER->value);
    }

    /**
     * 测试 CoinType 枚举的 label() 方法是否返回正确的可读名称
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举的 label() 方法是否返回正确的可读名称')]
    public function test_coin_type_label_method_returns_correct_names()
    {
        $this->assertEquals('未知', CoinType::TYPE_UNKNOWN->label());
        $this->assertEquals('签到', CoinType::TYPE_SIGN_IN->label());
        $this->assertEquals('邀请注册', CoinType::TYPE_INVITE_REGISTER->label());
    }

    /**
     * 测试 CoinType 枚举的 JsonSerializable 接口实现
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举的 JsonSerializable 接口实现')]
    public function test_coin_type_implements_json_serializable_correctly()
    {
        $coinType = CoinType::TYPE_SIGN_IN;
        $serialized = $coinType->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertEquals('sign_in', $serialized['value']);
        $this->assertEquals('签到', $serialized['label']);
    }

    /**
     * 测试 CoinType 枚举的 from() 方法是否正确工作
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举的 from() 方法是否正确工作')]
    public function test_coin_type_from_method_works_correctly()
    {
        $this->assertEquals(CoinType::TYPE_UNKNOWN, CoinType::from('unknown_type'));
        $this->assertEquals(CoinType::TYPE_SIGN_IN, CoinType::from('sign_in'));
        $this->assertEquals(CoinType::TYPE_INVITE_REGISTER, CoinType::from('invite_register'));
    }

    /**
     * 测试 CoinType 枚举的 tryFrom() 方法是否正确工作
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举的 tryFrom() 方法是否正确工作')]
    public function test_coin_type_try_from_method_works_correctly()
    {
        $this->assertEquals(CoinType::TYPE_UNKNOWN, CoinType::tryFrom('unknown_type'));
        $this->assertEquals(CoinType::TYPE_SIGN_IN, CoinType::tryFrom('sign_in'));
        $this->assertEquals(CoinType::TYPE_INVITE_REGISTER, CoinType::tryFrom('invite_register'));
        $this->assertNull(CoinType::tryFrom('nonexistent'));
    }

    /**
     * 测试 CoinType 枚举的 cases() 方法是否返回所有枚举值
     */
    #[Test]
    #[TestDox('测试 CoinType 枚举的 cases() 方法是否返回所有枚举值')]
    public function test_coin_type_cases_method_returns_all_values()
    {
        $cases = CoinType::cases();

        $this->assertCount(3, $cases);
        $this->assertEquals(CoinType::TYPE_UNKNOWN, $cases[0]);
        $this->assertEquals(CoinType::TYPE_SIGN_IN, $cases[1]);
        $this->assertEquals(CoinType::TYPE_INVITE_REGISTER, $cases[2]);
    }
}
