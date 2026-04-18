<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\Gender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Gender 枚举测试
 */
#[CoversClass(Gender::class)]
class GenderTest extends TestCase
{
    #[Test]
    #[TestDox('测试枚举值的正确性')]
    public function test_enum_values(): void
    {
        $this->assertEquals(0, Gender::GENDER_UNKNOWN->value);
        $this->assertEquals(1, Gender::GENDER_MALE->value);
        $this->assertEquals(2, Gender::GENDER_FEMALE->value);
    }

    #[Test]
    #[TestDox('测试 label() 方法返回正确的标签')]
    public function test_label_method(): void
    {
        $this->assertEquals('保密', Gender::GENDER_UNKNOWN->label());
        $this->assertEquals('男', Gender::GENDER_MALE->label());
        $this->assertEquals('女', Gender::GENDER_FEMALE->label());
    }

    #[Test]
    #[TestDox('测试 JsonSerializable 接口的实现')]
    public function test_json_serializable(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, Gender::GENDER_UNKNOWN);
        // 测试 jsonSerialize() 方法能够正常调用
        $unknownValue = Gender::GENDER_UNKNOWN->jsonSerialize();
        $maleValue = Gender::GENDER_MALE->jsonSerialize();
        $femaleValue = Gender::GENDER_FEMALE->jsonSerialize();

        // 验证返回值不为空
        $this->assertNotNull($unknownValue);
        $this->assertNotNull($maleValue);
        $this->assertNotNull($femaleValue);
    }

    #[Test]
    #[TestDox('测试从值创建枚举实例')]
    public function test_from_method(): void
    {
        $this->assertSame(Gender::GENDER_UNKNOWN, Gender::from(0));
        $this->assertSame(Gender::GENDER_MALE, Gender::from(1));
        $this->assertSame(Gender::GENDER_FEMALE, Gender::from(2));
    }

    #[Test]
    #[TestDox('测试 tryFrom 方法处理无效值')]
    public function test_try_from_method(): void
    {
        $this->assertSame(Gender::GENDER_UNKNOWN, Gender::tryFrom(0));
        $this->assertSame(Gender::GENDER_MALE, Gender::tryFrom(1));
        $this->assertSame(Gender::GENDER_FEMALE, Gender::tryFrom(2));
        $this->assertNull(Gender::tryFrom(-1));
        $this->assertNull(Gender::tryFrom(3));
    }

    #[Test]
    #[TestDox('测试所有枚举案例都能正确获取标签')]
    public function test_all_cases_have_labels(): void
    {
        foreach (Gender::cases() as $case) {
            $label = $case->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }

    #[Test]
    #[TestDox('测试枚举案例数量')]
    public function test_case_count(): void
    {
        $cases = Gender::cases();
        $this->assertCount(3, $cases);
    }
}
