<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Models\System\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(Setting::class)]
class SettingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试模型的基本属性和类型转换')]
    public function test_model_basic_attributes_and_casts(): void
    {
        // 创建测试数据
        $setting = Setting::create([
            'name' => '测试配置',
            'key' => 'test.key',
            'value' => 'test value',
            'cast_type' => 'string',
            'input_type' => 'text',
            'param' => '',
            'order' => 99,
            'remark' => '测试配置描述',
        ]);

        // 验证模型属性
        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertEquals('测试配置', $setting->name);
        $this->assertEquals('test.key', $setting->key);
        $this->assertEquals('test value', $setting->value);
        $this->assertEquals('string', $setting->cast_type);
        $this->assertEquals('text', $setting->input_type);
        $this->assertEquals(99, $setting->order);
        $this->assertEquals('测试配置描述', $setting->remark);
        $this->assertNotNull($setting->updated_at);
    }

    #[Test]
    #[TestDox('测试 getValueType 方法')]
    public function test_get_value_type_method(): void
    {
        // 创建测试数据
        Setting::create([
            'name' => '测试配置',
            'key' => 'test.key',
            'value' => 'test value',
            'cast_type' => 'integer',
            'input_type' => 'text',
        ]);

        // 测试存在的配置
        $this->assertEquals('integer', Setting::getValueType('test.key'));

        // 测试不存在的配置（使用默认值）
        $this->assertEquals('string', Setting::getValueType('non.existent.key'));
        $this->assertEquals('boolean', Setting::getValueType('non.existent.key', 'boolean'));
    }

    #[Test]
    #[TestDox('测试 getAll 方法')]
    public function test_get_all_method(): void
    {
        // 创建测试数据
        Setting::create([
            'name' => '配置1',
            'key' => 'config.one',
            'value' => 'value1',
            'order' => 10,
        ]);

        Setting::create([
            'name' => '配置2',
            'key' => 'config.two',
            'value' => 'value2',
            'order' => 5,
        ]);

        // 获取所有配置
        $settings = Setting::getAll();

        // 验证返回值是数组
        $this->assertIsArray($settings);
        // 验证包含预期的配置
        $this->assertArrayHasKey('config.one', $settings);
        $this->assertArrayHasKey('config.two', $settings);
        $this->assertEquals('value1', $settings['config.one']);
        $this->assertEquals('value2', $settings['config.two']);
    }

    #[Test]
    #[TestDox('测试 batchSet 方法')]
    public function test_batch_set_method(): void
    {
        // 准备批量数据
        $batchData = [
            [
                'name' => '批量配置1',
                'key' => 'batch.one',
                'value' => 'batch1',
                'cast_type' => 'string',
                'input_type' => 'text',
            ],
            [
                'name' => '批量配置2',
                'key' => 'batch.two',
                'value' => 'batch2',
                'cast_type' => 'integer',
                'input_type' => 'number',
            ],
        ];

        // 执行批量设置
        Setting::batchSet($batchData);

        // 验证数据是否正确插入
        $this->assertDatabaseHas('settings', [
            'key' => 'batch.one',
            'value' => 'batch1',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'batch.two',
            'value' => 'batch2',
        ]);
    }

    #[Test]
    #[TestDox('测试模型的可填充属性')]
    public function test_model_fillable_attributes(): void
    {
        // 测试所有可填充属性
        $setting = Setting::create([
            'name' => '可填充测试',
            'key' => 'fillable.test',
            'value' => 'fillable value',
            'cast_type' => 'string',
            'input_type' => 'text',
            'param' => 'test param',
            'order' => 50,
            'remark' => '可填充属性测试',
        ]);

        // 验证所有属性都被正确设置
        $this->assertEquals('可填充测试', $setting->name);
        $this->assertEquals('fillable.test', $setting->key);
        $this->assertEquals('fillable value', $setting->value);
        $this->assertEquals('string', $setting->cast_type);
        $this->assertEquals('text', $setting->input_type);
        $this->assertEquals('test param', $setting->param);
        $this->assertEquals(50, $setting->order);
        $this->assertEquals('可填充属性测试', $setting->remark);
    }
}
