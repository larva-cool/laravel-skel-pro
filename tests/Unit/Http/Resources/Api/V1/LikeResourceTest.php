<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\LikeResource;
use App\Models\Content\Like;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(LikeResource::class)]
#[TestDox('LikeResource 资源测试')]
class LikeResourceTest extends TestCase
{
    /**
     * 测试资源结构是否正确
     */
    #[Test]
    #[TestDox('测试资源结构')]
    public function test_resource_structure()
    {
        // 创建模拟的 Like 模型
        $like = $this->createLike();

        // 创建请求对象
        $request = new Request;

        // 创建资源并获取数组表示
        $resource = new LikeResource($like);
        $array = $resource->toArray($request);

        // 验证资源结构包含所有必要的键
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('source_id', $array);
        $this->assertArrayHasKey('source_type', $array);
        $this->assertArrayHasKey('extra', $array);
        $this->assertArrayHasKey('source', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);

        // 验证返回的是数组类型
        $this->assertIsArray($array);
    }

    /**
     * 测试字段值映射是否正确
     */
    #[Test]
    #[TestDox('测试字段值映射')]
    public function test_field_value_mapping()
    {
        // 准备测试数据
        $id = 1;
        $sourceId = 10;
        $sourceType = 'App\\Models\\Content\\Article';
        $extra = ['key' => 'value'];

        // 创建模拟的 Like 模型
        $like = $this->createLike([
            'id' => $id,
            'source_id' => $sourceId,
            'source_type' => $sourceType,
            'extra' => $extra,
        ]);

        // 创建请求对象
        $request = new Request;

        // 创建资源并获取数组表示
        $resource = new LikeResource($like);
        $array = $resource->toArray($request);

        // 验证字段值映射正确
        $this->assertEquals($id, $array['id']);
        $this->assertEquals($sourceId, $array['source_id']);
        $this->assertEquals($sourceType, $array['source_type']);
        $this->assertEquals($extra, $array['extra']);
    }

    /**
     * 测试日期格式化是否正确
     */
    #[Test]
    #[TestDox('测试日期格式化')]
    public function test_date_formatting()
    {
        // 准备测试数据
        $createdAt = Carbon::now();
        $updatedAt = Carbon::now();

        // 创建模拟的 Like 模型
        $like = $this->createLike([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        // 创建请求对象
        $request = new Request;

        // 创建资源并获取数组表示
        $resource = new LikeResource($like);
        $array = $resource->toArray($request);

        // 验证日期格式化正确
        $this->assertEquals($createdAt->toDateTimeString(), $array['created_at']);
        $this->assertEquals($updatedAt->toDateTimeString(), $array['updated_at']);
    }

    /**
     * 测试空日期处理
     */
    #[Test]
    #[TestDox('测试空日期处理')]
    public function test_null_date_handling()
    {
        // 创建模拟的 Like 模型
        $like = $this->createLike([
            'created_at' => null,
            'updated_at' => null,
        ]);

        // 创建请求对象
        $request = new Request;

        // 创建资源并获取数组表示
        $resource = new LikeResource($like);
        $array = $resource->toArray($request);

        // 验证空日期处理正确
        $this->assertNull($array['created_at']);
        $this->assertNull($array['updated_at']);
    }

    /**
     * 测试空值处理
     */
    #[Test]
    #[TestDox('测试空值处理')]
    public function test_null_values_handling()
    {
        // 创建模拟的 Like 模型
        $like = $this->createLike([
            'extra' => null,
        ]);

        // 创建请求对象
        $request = new Request;

        // 创建资源并获取数组表示
        $resource = new LikeResource($like);
        $array = $resource->toArray($request);

        // 验证空值处理正确
        $this->assertNull($array['extra']);
    }

    /**
     * 测试source字段存在性
     */
    #[Test]
    #[TestDox('测试source字段存在性')]
    public function test_source_field_exists()
    {
        // 创建模拟的 Like 模型
        $like = $this->createLike();

        // 创建请求对象
        $request = new Request;

        // 创建资源并获取数组表示
        $resource = new LikeResource($like);
        $array = $resource->toArray($request);

        // 只验证source字段存在于返回数组中
        $this->assertArrayHasKey('source', $array);
    }

    /**
     * 创建模拟的 Like 模型
     *
     * @param  array  $attributes  自定义属性
     * @return Mockery\MockInterface|Like
     */
    protected function createLike(array $attributes = [])
    {
        // 创建模拟的 Like 模型，使用 partial mock 以保留 Eloquent 模型的核心功能
        $like = Mockery::mock(Like::class)->makePartial();

        // 默认属性
        $defaults = [
            'id' => 1,
            'source_id' => 1,
            'source_type' => 'App\\Models\\Content\\Article',
            'extra' => [],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // 合并属性
        $data = array_merge($defaults, $attributes);

        // 设置属性数组
        $like->shouldReceive('attributes')->andReturn($data);

        // 设置getAttribute方法以正确处理属性访问
        $like->shouldReceive('getAttribute')
            ->with(Mockery::type('string'))
            ->andReturnUsing(function ($key) use ($data) {
                return $data[$key] ?? null;
            });

        // 为isAttributeNotNull方法设置预期
        foreach (['created_at', 'updated_at'] as $dateField) {
            if (isset($data[$dateField])) {
                $like->shouldReceive($dateField)->andReturn($data[$dateField]);
            }
        }

        return $like;
    }

    /**
     * 清理测试环境
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
