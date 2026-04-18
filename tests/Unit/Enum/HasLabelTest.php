<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\HasLabel;
use App\Enum\StatusSwitch;
use JsonSerializable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试使用 HasLabel trait 的枚举类
 */
enum TestEnum: int implements JsonSerializable
{
    use HasLabel;

    case ONE = 1;
    case TWO = 2;
    case THREE = 3;

    /**
     * 获取标签
     */
    public function label(): string
    {
        return match ($this) {
            self::ONE => '一',
            self::TWO => '二',
            self::THREE => '三',
        };
    }
}

/**
 * 测试没有 label 方法的枚举类
 */
enum TestEnumWithoutLabel: int implements JsonSerializable
{
    use HasLabel;

    case A = 1;
    case B = 2;
}

/**
 * HasLabel trait 测试
 */
#[TestDox('HasLabel trait 测试')]
class HasLabelTest extends TestCase
{
    /**
     * 测试 keys 方法
     */
    #[Test]
    #[TestDox('测试 keys 方法')]
    public function test_keys_method()
    {
        // 测试 TestEnum
        $expectedKeys = ['ONE', 'TWO', 'THREE'];
        $this->assertEquals($expectedKeys, TestEnum::keys());

        // 测试 TestEnumWithoutLabel
        $expectedKeysWithoutLabel = ['A', 'B'];
        $this->assertEquals($expectedKeysWithoutLabel, TestEnumWithoutLabel::keys());

        // 测试 StatusSwitch
        $expectedKeysStatusSwitch = ['DISABLED', 'ENABLED'];
        $this->assertEquals($expectedKeysStatusSwitch, StatusSwitch::keys());
    }

    /**
     * 测试 values 方法
     */
    #[Test]
    #[TestDox('测试 values 方法')]
    public function test_values_method()
    {
        // 测试 TestEnum
        $expectedValues = [1, 2, 3];
        $this->assertEquals($expectedValues, TestEnum::values());

        // 测试 TestEnumWithoutLabel
        $expectedValuesWithoutLabel = [1, 2];
        $this->assertEquals($expectedValuesWithoutLabel, TestEnumWithoutLabel::values());

        // 测试 StatusSwitch
        $expectedValuesStatusSwitch = [0, 1];
        $this->assertEquals($expectedValuesStatusSwitch, StatusSwitch::values());
    }

    /**
     * 测试 options 方法 - 有 label 方法
     */
    #[Test]
    #[TestDox('测试 options 方法 - 有 label 方法')]
    public function test_options_method_with_label()
    {
        // 测试 TestEnum
        $expectedOptions = [
            1 => '一',
            2 => '二',
            3 => '三',
        ];
        $this->assertEquals($expectedOptions, TestEnum::options());

        // 测试 StatusSwitch
        $expectedOptionsStatusSwitch = [
            0 => '停用',
            1 => '可用',
        ];
        $this->assertEquals($expectedOptionsStatusSwitch, StatusSwitch::options());
    }

    /**
     * 测试 options 方法 - 无 label 方法
     */
    #[Test]
    #[TestDox('测试 options 方法 - 无 label 方法')]
    public function test_options_method_without_label()
    {
        // 测试 TestEnumWithoutLabel
        $expectedOptions = [
            1 => 'A',
            2 => 'B',
        ];
        $this->assertEquals($expectedOptions, TestEnumWithoutLabel::options());
    }

    /**
     * 测试 jsonSerialize 方法
     */
    #[Test]
    #[TestDox('测试 jsonSerialize 方法')]
    public function test_json_serialize_method()
    {
        // 测试 TestEnum
        $oneJson = json_encode(TestEnum::ONE);
        $this->assertJson($oneJson);
        $oneData = json_decode($oneJson, true);
        $this->assertEquals(['value' => 1, 'label' => '一'], $oneData);

        // 测试 StatusSwitch
        $disabledJson = json_encode(StatusSwitch::DISABLED);
        $this->assertJson($disabledJson);
        $disabledData = json_decode($disabledJson, true);
        $this->assertEquals(['value' => 0, 'label' => '停用'], $disabledData);
    }
}
