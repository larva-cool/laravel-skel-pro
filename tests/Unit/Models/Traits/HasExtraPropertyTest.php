<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Traits;

use App\Models\Traits\HasExtraProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(HasExtraProperty::class)]
class HasExtraPropertyTest extends TestCase
{
    #[Test]
    #[TestDox('测试基本的设置和获取功能')]
    public function test_basic_extra()
    {
        // 创建测试模型实例
        $model = new class extends Model
        {
            use HasExtraProperty;

            protected $guarded = [];
        };

        // 设置测试数据
        $testExtra = [
            'bio' => 'Test bio',
            'website' => 'https://example.com',
            'social' => [
                'twitter' => '@test',
                'github' => 'testuser',
            ],
        ];

        // 设置 extra 属性
        $model->extra = $testExtra;

        // 验证 getExtra 方法返回正确的数组
        $extraArray = $model->getExtra();
        $this->assertIsArray($extraArray);
        $this->assertEquals($testExtra, $extraArray);

        // 验证获取的是 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->extra);

        // 验证 Fluent 对象的属性
        $this->assertEquals('Test bio', $model->extra->bio);
        $this->assertEquals('https://example.com', $model->extra->website);
        $this->assertEquals('@test', $model->extra->social['twitter']);
        $this->assertEquals('testuser', $model->extra->social['github']);
    }

    #[Test]
    #[TestDox('测试默认 extra 值')]
    public function test_default_extra()
    {
        // 创建测试模型实例，定义默认 extra 值
        $model = new class extends Model
        {
            use HasExtraProperty;

            protected $guarded = [];
            public const DEFAULT_EXTRA = [
                'bio' => 'Default bio',
                'website' => 'https://default.com',
                'social' => [
                    'twitter' => '@default',
                    'github' => 'defaultuser',
                ],
            ];
        };

        // 不设置 extra 属性，使用默认值

        // 验证 getExtra 方法返回默认值
        $extraArray = $model->getExtra();
        $this->assertIsArray($extraArray);
        $this->assertEquals('Default bio', $extraArray['bio']);
        $this->assertEquals('https://default.com', $extraArray['website']);
        $this->assertEquals('@default', $extraArray['social']['twitter']);
        $this->assertEquals('defaultuser', $extraArray['social']['github']);

        // 验证获取的是 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->extra);

        // 验证 Fluent 对象的属性
        $this->assertEquals('Default bio', $model->extra->bio);
        $this->assertEquals('https://default.com', $model->extra->website);
        $this->assertEquals('@default', $model->extra->social['twitter']);
        $this->assertEquals('defaultuser', $model->extra->social['github']);
    }

    #[Test]
    #[TestDox('测试默认值与设置值的合并')]
    public function test_extra_with_defaults()
    {
        // 创建测试模型实例，定义默认 extra 值
        $model = new class extends Model
        {
            use HasExtraProperty;

            protected $guarded = [];
            public const DEFAULT_EXTRA = [
                'bio' => 'Default bio',
                'website' => 'https://default.com',
                'social' => [
                    'twitter' => '@default',
                    'github' => 'defaultuser',
                ],
            ];
        };

        // 设置部分测试数据，覆盖默认值的一部分
        $testExtra = [
            'bio' => 'Custom bio',
            'social' => [
                'twitter' => '@custom',
            ],
        ];

        // 设置 extra 属性
        $model->extra = $testExtra;

        // 验证 getExtra 方法返回合并后的值
        $extraArray = $model->getExtra();
        $this->assertIsArray($extraArray);
        $this->assertEquals('Custom bio', $extraArray['bio']); // 覆盖默认值
        $this->assertEquals('https://default.com', $extraArray['website']); // 保留默认值
        $this->assertEquals('@custom', $extraArray['social']['twitter']); // 覆盖默认值的一部分
        $this->assertEquals('defaultuser', $extraArray['social']['github']); // 保留默认值的一部分

        // 验证获取的是 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->extra);

        // 验证 Fluent 对象的属性
        $this->assertEquals('Custom bio', $model->extra->bio);
        $this->assertEquals('https://default.com', $model->extra->website);
        $this->assertEquals('@custom', $model->extra->social['twitter']);
        $this->assertEquals('defaultuser', $model->extra->social['github']);
    }

    #[Test]
    #[TestDox('测试空 extra 值')]
    public function test_empty_extra()
    {
        // 创建测试模型实例
        $model = new class extends Model
        {
            use HasExtraProperty;

            protected $guarded = [];
        };

        // 不设置 extra 属性

        // 验证 getExtra 方法返回数组
        $extraArray = $model->getExtra();
        $this->assertIsArray($extraArray);

        // 验证获取的是 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->extra);
    }

    #[Test]
    #[TestDox('测试无效的 JSON 字符串')]
    public function test_invalid_json_extra()
    {
        // 创建测试模型实例
        $model = new class extends Model
        {
            use HasExtraProperty;

            protected $guarded = [];
        };

        // 手动设置无效的 JSON 字符串
        $model->setRawAttributes(['extra' => 'invalid json']);

        // 验证 getExtra 方法返回数组
        $extraArray = $model->getExtra();
        $this->assertIsArray($extraArray);

        // 验证获取的是 Fluent 对象
        $this->assertInstanceOf(Fluent::class, $model->extra);
    }
}
