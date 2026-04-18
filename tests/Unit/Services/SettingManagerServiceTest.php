<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enum\CacheKey;
use App\Models\System\Setting;
use App\Services\SettingManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(SettingManagerService::class)]
#[TestDox('测试 SettingManagerService 类的设置管理服务方法')]
class SettingManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettingManagerService $service;

    /**
     * 测试初始化
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SettingManagerService;
    }

    /**
     * 测试构造函数是否正确初始化settings集合
     */
    #[Test]
    #[TestDox('测试构造函数初始化 settings 集合不为空')]
    public function test_constructor_initializes_settings_collection()
    {
        $this->assertInstanceOf(Collection::class, $this->service->all());
        $this->assertNotEmpty($this->service->all());
    }

    /**
     * 测试all()方法是否从数据库加载配置
     */
    #[Test]
    #[TestDox('测试 all 方法加载数据库中的所有设置')]
    public function test_all_loads_settings_from_database()
    {
        // 创建测试数据
        Setting::create([
            'name' => '站点名称',
            'key' => 'site.name',
            'value' => '测试站点',
            'cast_type' => 'string',
            'order' => 1,
            'remark' => '网站名称',
        ]);
        Setting::create([
            'name' => '站点版本',
            'key' => 'site.version',
            'value' => '1.0.0',
            'cast_type' => 'string',
            'order' => 2,
            'remark' => '网站版本',
        ]);

        // 调用all方法
        $settings = $this->service->all();

        // 调试信息 - 打印实际结构
        // var_dump($settings);

        // 验证结果
        // 注意：根据Service实现，这里可能有不同的结构
        $this->assertTrue(isset($settings['site']));
        $this->assertIsArray($settings['site']);
        $this->assertCount(2, $settings['site']);
        $this->assertEquals('测试站点', $settings['site']['name']);
        $this->assertEquals('1.0.0', $settings['site']['version']);
    }

    /**
     * 测试all()方法是否使用缓存
     */
    #[Test]
    #[TestDox('测试 all 方法使用缓存加载设置')]
    public function test_all_uses_cache()
    {
        // 模拟缓存数据
        Cache::shouldReceive('get')
            ->with(CacheKey::SETTINGS)
            ->andReturn(['site.name' => '缓存站点']);

        Cache::shouldReceive('put')->never();

        // 调用all方法
        $settings = $this->service->all();

        // 验证结果
        $this->assertEquals('缓存站点', $settings['site.name']);
    }

    /**
     * 测试all()方法在强制重载时更新缓存
     */
    #[Test]
    #[TestDox('测试 all 方法在强制重载时更新缓存')]
    public function test_all_refreshes_cache_when_forced()
    {
        // 创建测试数据
        Setting::create([
            'name' => '站点名称',
            'key' => 'site.name',
            'value' => '新站点',
            'cast_type' => 'string',
            'order' => 1,
            'remark' => '网站名称',
        ]);

        // 第一次调用all方法加载数据到缓存
        $this->service->all();

        // 修改数据库中的数据
        Setting::where('key', 'site.name')->update(['value' => '更新后的站点']);

        // 强制重载
        $settings = $this->service->all(true);

        // 验证结果
        $this->assertEquals('更新后的站点', $settings['site.name']);
    }

    /**
     * 测试get()方法获取配置值
     */
    #[Test]
    #[TestDox('测试 get 方法返回配置值')]
    public function test_get_returns_setting_value()
    {
        // 创建测试数据
        Setting::create([
            'name' => '站点名称',
            'key' => 'site.name',
            'value' => '测试站点',
            'cast_type' => 'string',
            'order' => 1,
            'remark' => '网站名称',
        ]);

        // 测试存在的配置
        $this->assertEquals('测试站点', $this->service->get('site.name'));

        // 测试不存在的配置，使用默认值
        $this->assertEquals('默认值', $this->service->get('non.existent', '默认值'));
    }

    /**
     * 测试has()方法判断配置是否存在
     */
    #[Test]
    #[TestDox('测试 has 方法判断配置是否存在')]
    public function test_has_checks_setting_existence()
    {
        // 创建测试数据
        Setting::create([
            'name' => '站点名称',
            'key' => 'site.name',
            'value' => '测试站点',
            'cast_type' => 'string',
            'order' => 1,
            'remark' => '网站名称',
        ]);

        // 测试存在的配置
        $this->assertTrue($this->service->has('site.name'));

        // 测试不存在的配置
        $this->assertFalse($this->service->has('non.existent'));
    }

    /**
     * 测试tag()方法获取配置组
     */
    #[Test]
    #[TestDox('测试 tag 方法返回配置组')]
    public function test_tag_returns_settings_group()
    {
        // 创建测试数据
        Setting::create([
            'name' => '站点名称',
            'key' => 'site.name',
            'value' => '测试站点',
            'cast_type' => 'string',
            'order' => 1,
            'remark' => '网站名称',
        ]);
        Setting::create([
            'name' => '站点版本',
            'key' => 'site.version',
            'value' => '1.0.0',
            'cast_type' => 'string',
            'order' => 2,
            'remark' => '网站版本',
        ]);

        // 测试获取配置组
        $siteSettings = $this->service->tag('site');
        $this->assertIsArray($siteSettings);
        $this->assertEquals('测试站点', $siteSettings['name']);
        $this->assertEquals('1.0.0', $siteSettings['version']);

        // 额外测试直接从all()方法获取嵌套结构
        $settings = $this->service->all();
        $this->assertEquals('测试站点', $settings['site']['name']);
    }

    /**
     * 测试set()方法保存配置
     */
    #[Test]
    #[TestDox('测试 set 方法保存配置')]
    public function test_set_saves_setting()
    {
        // 测试新增配置
        $this->assertTrue($this->service->set('site.name', '新站点', 'string'));
        $this->assertEquals('新站点', $this->service->get('site.name'));

        // 测试更新配置
        $this->assertTrue($this->service->set('site.name', '更新站点', 'string'));
        $this->assertEquals('更新站点', $this->service->get('site.name'));

        // 测试数组值（应该返回false）
        $this->assertFalse($this->service->set('site.config', ['key' => 'value'], 'string'));
    }

    /**
     * 测试forge()方法删除配置
     */
    #[Test]
    #[TestDox('测试 forge 方法删除配置')]
    public function test_forge_deletes_setting()
    {
        // 创建测试数据
        Setting::create([
            'name' => '站点名称',
            'key' => 'site.name',
            'value' => '测试站点',
            'cast_type' => 'string',
            'order' => 1,
            'remark' => '网站名称',
        ]);

        // 测试删除配置
        $this->assertTrue($this->service->forge('site.name'));
        $this->assertFalse($this->service->has('site.name'));
    }

    /**
     * 测试castTypes()方法获取配置项类型
     */
    #[Test]
    #[TestDox('测试 castTypes 方法返回配置项类型')]
    public function test_cast_types_returns_setting_types()
    {
        // 创建测试数据
        Setting::create([
            'name' => '站点名称',
            'key' => 'site.name',
            'value' => '测试站点',
            'cast_type' => 'string',
            'order' => 1,
            'remark' => '网站名称',
        ]);
        Setting::create([
            'name' => '站点激活状态',
            'key' => 'site.active',
            'value' => '1',
            'cast_type' => 'bool',
            'order' => 2,
            'remark' => '站点是否激活',
        ]);

        // 测试获取配置类型
        $castTypes = $this->service->castTypes();
        $this->assertIsArray($castTypes);
        $this->assertEquals('string', $castTypes['site.name']);
        $this->assertEquals('bool', $castTypes['site.active']);
    }

    /**
     * 测试配置值的类型转换
     */
    #[Test]
    #[TestDox('测试配置值的类型转换')]
    public function test_setting_value_casting()
    {
        // 创建不同类型的测试数据
        Setting::create([
            'name' => '整数配置',
            'key' => 'int.setting',
            'value' => '123',
            'cast_type' => 'int',
            'order' => 1,
            'remark' => '整数类型配置',
        ]);
        Setting::create([
            'name' => '浮点数配置',
            'key' => 'float.setting',
            'value' => '123.45',
            'cast_type' => 'float',
            'order' => 2,
            'remark' => '浮点数类型配置',
        ]);
        Setting::create([
            'name' => '布尔值配置',
            'key' => 'bool.setting',
            'value' => '1',
            'cast_type' => 'bool',
            'order' => 3,
            'remark' => '布尔类型配置',
        ]);
        Setting::create([
            'name' => '字符串配置',
            'key' => 'string.setting',
            'value' => 'string value',
            'cast_type' => 'string',
            'order' => 4,
            'remark' => '字符串类型配置',
        ]);

        // 验证类型转换
        $this->assertIsInt($this->service->get('int.setting'));
        $this->assertEquals(123, $this->service->get('int.setting'));

        $this->assertIsFloat($this->service->get('float.setting'));
        $this->assertEquals(123.45, $this->service->get('float.setting'));

        $this->assertIsBool($this->service->get('bool.setting'));
        $this->assertTrue($this->service->get('bool.setting'));

        $this->assertIsString($this->service->get('string.setting'));
        $this->assertEquals('string value', $this->service->get('string.setting'));
    }
}
