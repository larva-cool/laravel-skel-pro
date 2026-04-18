<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\CacheKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CacheKey::class)]
class CacheKeyTest extends TestCase
{
    #[Test]
    #[TestDox('测试 SETTINGS 常量是否正确定义')]
    public function test_settings_constant_is_correctly_defined(): void
    {
        $this->assertEquals('system:settings', CacheKey::SETTINGS);
    }

    #[Test]
    #[TestDox('测试 DICT_TYPE 常量是否正确定义')]
    public function test_dict_type_constant_is_correctly_defined(): void
    {
        $this->assertEquals('system:dicts:%s', CacheKey::DICT_TYPE);
    }

    #[Test]
    #[TestDox('测试 DICT_TYPE 常量的格式化功能')]
    public function test_dict_type_constant_can_be_formatted(): void
    {
        $type = 'user_status';
        $formattedKey = sprintf(CacheKey::DICT_TYPE, $type);

        $this->assertEquals('system:dicts:user_status', $formattedKey);
    }

    #[Test]
    #[TestDox('测试多个不同类型的 DICT_TYPE 格式化')]
    public function test_dict_type_constant_formats_different_types(): void
    {
        $testCases = [
            ['type' => 'user_role', 'expected' => 'system:dicts:user_role'],
            ['type' => 'order_status', 'expected' => 'system:dicts:order_status'],
            ['type' => 'product_category', 'expected' => 'system:dicts:product_category'],
        ];

        foreach ($testCases as $testCase) {
            $formattedKey = sprintf(CacheKey::DICT_TYPE, $testCase['type']);
            $this->assertEquals($testCase['expected'], $formattedKey);
        }
    }
}
