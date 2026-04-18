<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\StatusSwitch;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * StatusSwitch 枚举测试
 */
#[TestDox('StatusSwitch 枚举测试')]
class StatusSwitchTest extends TestCase
{
    /**
     * 测试枚举值
     */
    #[Test]
    #[TestDox('测试枚举值')]
    public function test_enum_values()
    {
        // 测试 DISABLED 值
        $this->assertEquals(0, StatusSwitch::DISABLED->value);

        // 测试 ENABLED 值
        $this->assertEquals(1, StatusSwitch::ENABLED->value);
    }

    /**
     * 测试 label 方法
     */
    #[Test]
    #[TestDox('测试 label 方法')]
    public function test_label_method()
    {
        // 测试 DISABLED 标签
        $this->assertEquals('停用', StatusSwitch::DISABLED->label());

        // 测试 ENABLED 标签
        $this->assertEquals('可用', StatusSwitch::ENABLED->label());
    }

    /**
     * 测试 isEnabled 方法
     */
    #[Test]
    #[TestDox('测试 isEnabled 方法')]
    public function test_is_enabled_method()
    {
        // 测试 DISABLED 状态
        $this->assertFalse(StatusSwitch::DISABLED->isEnabled());

        // 测试 ENABLED 状态
        $this->assertTrue(StatusSwitch::ENABLED->isEnabled());
    }

    /**
     * 测试 toggle 方法
     */
    #[Test]
    #[TestDox('测试 toggle 方法')]
    public function test_toggle_method()
    {
        // 测试从 DISABLED 切换到 ENABLED
        $this->assertEquals(StatusSwitch::ENABLED, StatusSwitch::DISABLED->toggle());

        // 测试从 ENABLED 切换到 DISABLED
        $this->assertEquals(StatusSwitch::DISABLED, StatusSwitch::ENABLED->toggle());
    }

    /**
     * 测试 JsonSerializable 接口实现
     */
    #[Test]
    #[TestDox('测试 JsonSerializable 接口实现')]
    public function test_json_serializable()
    {
        // 测试 DISABLED 的 JSON 序列化
        $disabledJson = json_encode(StatusSwitch::DISABLED);
        $this->assertJson($disabledJson);
        $disabledData = json_decode($disabledJson, true);
        $this->assertEquals(['value' => 0, 'label' => '停用'], $disabledData);

        // 测试 ENABLED 的 JSON 序列化
        $enabledJson = json_encode(StatusSwitch::ENABLED);
        $this->assertJson($enabledJson);
        $enabledData = json_decode($enabledJson, true);
        $this->assertEquals(['value' => 1, 'label' => '可用'], $enabledData);
    }

    /**
     * 测试通过值获取枚举实例
     */
    #[Test]
    #[TestDox('测试通过值获取枚举实例')]
    public function test_from_value()
    {
        // 测试从值 0 获取 DISABLED
        $this->assertEquals(StatusSwitch::DISABLED, StatusSwitch::from(0));

        // 测试从值 1 获取 ENABLED
        $this->assertEquals(StatusSwitch::ENABLED, StatusSwitch::from(1));
    }

    /**
     * 测试 tryFrom 方法
     */
    #[Test]
    #[TestDox('测试 tryFrom 方法')]
    public function test_try_from()
    {
        // 测试有效的值
        $this->assertEquals(StatusSwitch::DISABLED, StatusSwitch::tryFrom(0));
        $this->assertEquals(StatusSwitch::ENABLED, StatusSwitch::tryFrom(1));

        // 测试无效的值
        $this->assertNull(StatusSwitch::tryFrom(2));
        $this->assertNull(StatusSwitch::tryFrom(-1));
    }
}
