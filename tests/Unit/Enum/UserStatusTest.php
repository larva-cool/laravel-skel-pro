<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\UserStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * UserStatus 枚举测试
 */
#[CoversClass(UserStatus::class)]
class UserStatusTest extends TestCase
{
    #[Test]
    #[TestDox('测试枚举值的正确性')]
    public function test_enum_values(): void
    {
        $this->assertEquals(0, UserStatus::STATUS_ACTIVE->value);
        $this->assertEquals(1, UserStatus::STATUS_FROZEN->value);
        $this->assertEquals(2, UserStatus::STATUS_NOT_ACTIVE->value);
    }

    #[Test]
    #[TestDox('测试 label() 方法返回正确的标签')]
    public function test_label_method(): void
    {
        $this->assertEquals('正常', UserStatus::STATUS_ACTIVE->label());
        $this->assertEquals('已冻结', UserStatus::STATUS_FROZEN->label());
        $this->assertEquals('未激活', UserStatus::STATUS_NOT_ACTIVE->label());
    }

    #[Test]
    #[TestDox('测试 isActive() 方法')]
    public function test_is_active_method(): void
    {
        $this->assertTrue(UserStatus::STATUS_ACTIVE->isActive());
        $this->assertFalse(UserStatus::STATUS_FROZEN->isActive());
        $this->assertFalse(UserStatus::STATUS_NOT_ACTIVE->isActive());
    }

    #[Test]
    #[TestDox('测试 isFrozen() 方法')]
    public function test_is_frozen_method(): void
    {
        $this->assertFalse(UserStatus::STATUS_ACTIVE->isFrozen());
        $this->assertTrue(UserStatus::STATUS_FROZEN->isFrozen());
        $this->assertFalse(UserStatus::STATUS_NOT_ACTIVE->isFrozen());
    }

    #[Test]
    #[TestDox('测试 JsonSerializable 接口的实现')]
    public function test_json_serializable(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, UserStatus::STATUS_ACTIVE);
        // 测试 jsonSerialize() 方法能够正常调用
        $activeValue = UserStatus::STATUS_ACTIVE->jsonSerialize();
        $frozenValue = UserStatus::STATUS_FROZEN->jsonSerialize();
        $notActiveValue = UserStatus::STATUS_NOT_ACTIVE->jsonSerialize();

        // 验证返回值不为空
        $this->assertNotNull($activeValue);
        $this->assertNotNull($frozenValue);
        $this->assertNotNull($notActiveValue);
    }

    #[Test]
    #[TestDox('测试从值创建枚举实例')]
    public function test_from_method(): void
    {
        $this->assertSame(UserStatus::STATUS_ACTIVE, UserStatus::from(0));
        $this->assertSame(UserStatus::STATUS_FROZEN, UserStatus::from(1));
        $this->assertSame(UserStatus::STATUS_NOT_ACTIVE, UserStatus::from(2));
    }

    #[Test]
    #[TestDox('测试 tryFrom 方法处理无效值')]
    public function test_try_from_method(): void
    {
        $this->assertSame(UserStatus::STATUS_ACTIVE, UserStatus::tryFrom(0));
        $this->assertSame(UserStatus::STATUS_FROZEN, UserStatus::tryFrom(1));
        $this->assertSame(UserStatus::STATUS_NOT_ACTIVE, UserStatus::tryFrom(2));
        $this->assertNull(UserStatus::tryFrom(-1));
        $this->assertNull(UserStatus::tryFrom(3));
    }

    #[Test]
    #[TestDox('测试所有枚举案例都能正确获取标签')]
    public function test_all_cases_have_labels(): void
    {
        foreach (UserStatus::cases() as $case) {
            $label = $case->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }

    #[Test]
    #[TestDox('测试枚举案例数量')]
    public function test_case_count(): void
    {
        $cases = UserStatus::cases();
        $this->assertCount(3, $cases);
    }
}
