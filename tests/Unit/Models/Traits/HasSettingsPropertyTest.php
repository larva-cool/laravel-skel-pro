<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Traits;

use App\Models\Traits\HasSettingsProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 测试 HasSettingsProperty 特质
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(HasSettingsProperty::class)]
class HasSettingsPropertyTest extends TestCase
{
    /**
     * 测试基本的设置和获取功能
     */
    #[Test]
    #[TestDox('测试基本的设置和获取功能')]
    public function test_basic_settings()
    {
        // 创建测试模型实例
        $model = new class extends Model
        {
            use HasSettingsProperty;

            protected $guarded = [];
        };

        // 设置测试数据
        $testSettings = [
            'theme' => 'dark',
            'notifications' => true,
            'language' => 'zh-CN',
        ];

        // 设置 settings 属性
        $model->settings = $testSettings;

        // 验证 getSettings 方法返回正确的数组
        $settingsArray = $model->getSettings();
        $this->assertIsArray($settingsArray);
        $this->assertEquals($testSettings, $settingsArray);

        // 验证获取的是 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->settings);

        // 验证 Fluent 对象的属性
        $this->assertEquals('dark', $model->settings->theme);
        $this->assertEquals(true, $model->settings->notifications);
        $this->assertEquals('zh-CN', $model->settings->language);

        // 验证 getSettings 方法返回的是数组
        $settingsArray = $model->getSettings();
        $this->assertIsArray($settingsArray);
        $this->assertEquals($testSettings, $settingsArray);
    }

    /**
     * 测试默认设置的合并功能
     */
    #[Test]
    #[TestDox('测试默认设置的合并功能')]
    public function test_default_settings_merging()
    {
        // 创建带有默认设置的测试模型
        $model = new class extends Model
        {
            use HasSettingsProperty;

            protected $guarded = [];
            public const DEFAULT_SETTINGS = [
                'theme' => 'light',
                'notifications' => false,
                'language' => 'en-US',
                'timezone' => 'UTC',
            ];
        };

        // 设置部分覆盖的设置
        $customSettings = [
            'theme' => 'dark',
            'language' => 'zh-CN',
        ];

        $model->settings = $customSettings;

        // 验证合并结果
        $mergedSettings = $model->getSettings();
        $this->assertEquals('dark', $mergedSettings['theme']); // 自定义值
        $this->assertEquals('zh-CN', $mergedSettings['language']); // 自定义值
        $this->assertEquals(false, $mergedSettings['notifications']); // 默认值
        $this->assertEquals('UTC', $mergedSettings['timezone']); // 默认值

        // 验证 Fluent 对象
        $this->assertEquals('dark', $model->settings->theme);
        $this->assertEquals('zh-CN', $model->settings->language);
        $this->assertEquals(false, $model->settings->notifications);
        $this->assertEquals('UTC', $model->settings->timezone);
    }

    /**
     * 测试空设置的处理
     */
    #[Test]
    #[TestDox('测试空设置的处理')]
    public function test_empty_settings()
    {
        // 创建测试模型
        $model = new class extends Model
        {
            use HasSettingsProperty;

            protected $guarded = [];
        };

        // 设置空数组
        $model->settings = [];

        // 验证 getSettings 方法返回空数组
        $settingsArray = $model->getSettings();
        $this->assertIsArray($settingsArray);
        $this->assertEquals([], $settingsArray);

        // 验证获取的是空的 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->settings);
        $this->assertEquals([], $model->getSettings());
    }

    /**
     * 测试未设置 settings 的情况
     */
    #[Test]
    #[TestDox('测试未设置 settings 的情况')]
    public function test_unset_settings()
    {
        // 创建测试模型
        $model = new class extends Model
        {
            use HasSettingsProperty;

            protected $guarded = [];
            public const DEFAULT_SETTINGS = [
                'theme' => 'light',
                'notifications' => false,
            ];
        };

        // 验证未设置 settings 时返回默认设置
        $settings = $model->getSettings();
        $this->assertEquals('light', $settings['theme']);
        $this->assertEquals(false, $settings['notifications']);

        // 验证 Fluent 对象
        $this->assertEquals('light', $model->settings->theme);
        $this->assertEquals(false, $model->settings->notifications);
    }

    /**
     * 测试没有默认设置的情况
     */
    #[Test]
    #[TestDox('测试没有默认设置的情况')]
    public function test_no_default_settings()
    {
        // 创建没有默认设置的测试模型
        $model = new class extends Model
        {
            use HasSettingsProperty;

            protected $guarded = [];
        };

        // 验证没有默认设置时返回空数组
        $settings = $model->getSettings();
        $this->assertEquals([], $settings);

        // 验证 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->settings);
    }
}
