<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Traits;

use App\Models\Traits\DateTimeFormatter;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * DateTimeFormatter trait 测试
 */
#[TestDox('DateTimeFormatter trait 测试')]
class DateTimeFormatterTest extends TestCase
{
    /**
     * 测试 serializeDate 方法
     */
    #[Test]
    #[TestDox('测试 serializeDate 方法')]
    public function test_serialize_date()
    {
        // 创建一个使用 DateTimeFormatter trait 的测试类
        $testClass = new class
        {
            use DateTimeFormatter;

            protected $dateFormat = 'Y-m-d H:i:s';

            // 模拟 getDateFormat 方法
            public function getDateFormat()
            {
                return $this->dateFormat;
            }
        };

        // 创建一个 Carbon 日期实例
        $date = Carbon::create(2023, 1, 1, 12, 0, 0);

        // 使用反射获取并调用 serializeDate 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('serializeDate');
        $method->setAccessible(true);

        // 调用方法并获取结果
        $result = $method->invoke($testClass, $date);

        // 验证结果是否正确
        $this->assertIsString($result);
        $this->assertEquals($date->format('Y-m-d H:i:s'), $result);
    }

    /**
     * 测试不同日期格式
     */
    #[Test]
    #[TestDox('测试不同日期格式')]
    public function test_different_date_formats()
    {
        // 创建一个使用 DateTimeFormatter trait 并自定义日期格式的测试类
        $testClass = new class
        {
            use DateTimeFormatter;

            protected $dateFormat = 'Y-m-d';

            // 模拟 getDateFormat 方法
            public function getDateFormat()
            {
                return $this->dateFormat;
            }
        };

        // 创建一个 Carbon 日期实例
        $date = Carbon::create(2023, 1, 1, 12, 0, 0);

        // 使用反射获取并调用 serializeDate 方法
        $reflection = new \ReflectionClass($testClass);
        $method = $reflection->getMethod('serializeDate');
        $method->setAccessible(true);

        // 调用方法并获取结果
        $result = $method->invoke($testClass, $date);

        // 验证结果是否正确
        $this->assertIsString($result);
        $this->assertEquals($date->format('Y-m-d'), $result);
    }

    /**
     * 测试 trait 是否被正确使用
     */
    #[Test]
    #[TestDox('测试 trait 是否被正确使用')]
    public function test_trait_usage()
    {
        // 创建一个使用 DateTimeFormatter trait 的测试类
        $testClass = new class
        {
            use DateTimeFormatter;

            protected $dateFormat = 'Y-m-d H:i:s';

            // 模拟 getDateFormat 方法
            public function getDateFormat()
            {
                return $this->dateFormat;
            }
        };

        // 验证 trait 是否被正确使用
        $usedTraits = array_keys(class_uses_recursive(get_class($testClass)));
        $this->assertContains('App\Models\Traits\DateTimeFormatter', $usedTraits);

        // 验证 serializeDate 方法是否存在
        $reflection = new \ReflectionClass($testClass);
        $this->assertTrue($reflection->hasMethod('serializeDate'));
    }
}
