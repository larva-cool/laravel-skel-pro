<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\SettingType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingType::class)]
class SettingTypeTest extends TestCase
{
    #[Test]
    #[TestDox('测试 CAST_TYPE_INT 常量是否正确定义')]
    public function test_cast_type_int_constant_is_correctly_defined(): void
    {
        $this->assertEquals('int', SettingType::CAST_TYPE_INT);
    }

    #[Test]
    #[TestDox('测试 CAST_TYPE_FLOAT 常量是否正确定义')]
    public function test_cast_type_float_constant_is_correctly_defined(): void
    {
        $this->assertEquals('float', SettingType::CAST_TYPE_FLOAT);
    }

    #[Test]
    #[TestDox('测试 CAST_TYPE_BOOL 常量是否正确定义')]
    public function test_cast_type_bool_constant_is_correctly_defined(): void
    {
        $this->assertEquals('bool', SettingType::CAST_TYPE_BOOL);
    }

    #[Test]
    #[TestDox('测试 CAST_TYPE_STRING 常量是否正确定义')]
    public function test_cast_type_string_constant_is_correctly_defined(): void
    {
        $this->assertEquals('string', SettingType::CAST_TYPE_STRING);
    }

    #[Test]
    #[TestDox('测试所有类型常量是否都是字符串类型')]
    public function test_all_type_constants_are_string_type(): void
    {
        $this->assertIsString(SettingType::CAST_TYPE_INT);
        $this->assertIsString(SettingType::CAST_TYPE_FLOAT);
        $this->assertIsString(SettingType::CAST_TYPE_BOOL);
        $this->assertIsString(SettingType::CAST_TYPE_STRING);
    }
}
