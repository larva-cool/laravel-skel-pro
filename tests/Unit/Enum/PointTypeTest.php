<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\PointType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(PointType::class)]
class PointTypeTest extends TestCase
{
    #[Test]
    #[TestDox('测试 PointType 枚举是否正确实现 JsonSerializable 接口')]
    public function test_point_type_implements_json_serializable()
    {
        $pointType = PointType::TYPE_SIGN_IN;

        $this->assertInstanceOf('JsonSerializable', $pointType);
    }

    #[Test]
    #[TestDox('测试 PointType 枚举的所有值是否正确定义')]
    public function test_point_type_values_are_correctly_defined()
    {
        $this->assertTrue(defined('App\\Enum\\PointType::TYPE_SIGN_IN'));
        $this->assertTrue(defined('App\\Enum\\PointType::TYPE_INVITE_REGISTER'));
        $this->assertTrue(defined('App\\Enum\\PointType::TYPE_SET_UP_AVATAR'));
        $this->assertTrue(defined('App\\Enum\\PointType::TYPE_RECOVERY'));
    }

    #[Test]
    #[TestDox('测试 PointType 枚举的 value 属性是否正确')]
    public function test_point_type_value_properties_are_correct()
    {
        $this->assertEquals('sign_in', PointType::TYPE_SIGN_IN->value);
        $this->assertEquals('invite_register', PointType::TYPE_INVITE_REGISTER->value);
        $this->assertEquals('modify_avatar', PointType::TYPE_SET_UP_AVATAR->value);
        $this->assertEquals('recovery', PointType::TYPE_RECOVERY->value);
    }

    #[Test]
    #[TestDox('测试 PointType 枚举的 label() 方法是否返回正确的可读名称')]
    public function test_point_type_label_method_returns_correct_names()
    {
        $this->assertEquals('签到', PointType::TYPE_SIGN_IN->label());
        $this->assertEquals('邀请注册', PointType::TYPE_INVITE_REGISTER->label());
        $this->assertEquals('设置头像', PointType::TYPE_SET_UP_AVATAR->label());
        $this->assertEquals('过期回收', PointType::TYPE_RECOVERY->label());
    }

    #[Test]
    #[TestDox('测试 PointType 枚举的 JsonSerializable 接口实现')]
    public function test_point_type_implements_json_serializable_correctly()
    {
        $pointType = PointType::TYPE_SIGN_IN;
        $serialized = $pointType->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertEquals('sign_in', $serialized['value']);
        $this->assertEquals('签到', $serialized['label']);
    }

    #[Test]
    #[TestDox('测试 PointType 枚举的 from() 方法是否正确工作')]
    public function test_point_type_from_method_works_correctly()
    {
        $this->assertEquals(PointType::TYPE_SIGN_IN, PointType::from('sign_in'));
        $this->assertEquals(PointType::TYPE_INVITE_REGISTER, PointType::from('invite_register'));
        $this->assertEquals(PointType::TYPE_SET_UP_AVATAR, PointType::from('modify_avatar'));
        $this->assertEquals(PointType::TYPE_RECOVERY, PointType::from('recovery'));
    }

    #[Test]
    #[TestDox('测试 PointType 枚举的 tryFrom() 方法是否正确工作')]
    public function test_point_type_try_from_method_works_correctly()
    {
        $this->assertEquals(PointType::TYPE_SIGN_IN, PointType::tryFrom('sign_in'));
        $this->assertEquals(PointType::TYPE_INVITE_REGISTER, PointType::tryFrom('invite_register'));
        $this->assertEquals(PointType::TYPE_SET_UP_AVATAR, PointType::tryFrom('modify_avatar'));
        $this->assertEquals(PointType::TYPE_RECOVERY, PointType::tryFrom('recovery'));
        $this->assertNull(PointType::tryFrom('nonexistent'));
    }

    #[Test]
    #[TestDox('测试 PointType 枚举的 cases() 方法是否返回所有枚举值')]
    public function test_point_type_cases_method_returns_all_values()
    {
        $cases = PointType::cases();

        $this->assertCount(4, $cases);
        $this->assertEquals(PointType::TYPE_SIGN_IN, $cases[0]);
        $this->assertEquals(PointType::TYPE_INVITE_REGISTER, $cases[1]);
        $this->assertEquals(PointType::TYPE_SET_UP_AVATAR, $cases[2]);
        $this->assertEquals(PointType::TYPE_RECOVERY, $cases[3]);
    }
}
