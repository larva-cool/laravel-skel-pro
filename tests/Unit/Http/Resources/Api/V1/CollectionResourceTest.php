<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\CollectionResource;
use App\Models\Content\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(CollectionResource::class)]
#[TestDox('CollectionResource 测试')]
class CollectionResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试资源结构是否正确')]
    public function test_resource_structure(): void
    {
        $collection = $this->createCollection();
        $resource = new CollectionResource($collection);
        $request = Mockery::mock(Request::class);

        $array = $resource->toArray($request);

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('source_id', $array);
        $this->assertArrayHasKey('source_type', $array);
        $this->assertArrayHasKey('extra', $array);
        $this->assertArrayHasKey('source', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    #[Test]
    #[TestDox('测试日期格式是否正确')]
    public function test_date_formats(): void
    {
        $createdAt = Carbon::create(2023, 5, 15, 10, 30, 0);
        $updatedAt = Carbon::create(2023, 5, 15, 11, 45, 0);

        $collection = $this->createCollection([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        $resource = new CollectionResource($collection);
        $request = Mockery::mock(Request::class);

        $array = $resource->toArray($request);

        $this->assertEquals($createdAt->toDateTimeString(), $array['created_at']);
        $this->assertEquals($updatedAt->toDateTimeString(), $array['updated_at']);
    }

    #[Test]
    #[TestDox('测试null日期处理')]
    public function test_null_date_handling(): void
    {
        $collection = $this->createCollection([
            'created_at' => null,
            'updated_at' => null,
        ]);

        $resource = new CollectionResource($collection);
        $request = Mockery::mock(Request::class);

        $array = $resource->toArray($request);

        $this->assertNull($array['created_at']);
        $this->assertNull($array['updated_at']);
    }

    /**
     * 创建测试用的 Collection 实例
     *
     * @param  array  $attributes  额外的属性
     * @param  mixed  $source  可选的source对象
     */
    private function createCollection(array $attributes = [], $source = null): Collection
    {
        $hasNullCreatedAt = array_key_exists('created_at', $attributes) && $attributes['created_at'] === null;
        $hasNullUpdatedAt = array_key_exists('updated_at', $attributes) && $attributes['updated_at'] === null;

        // 移除可能不在 fillable 中的属性
        $fillableAttributes = $attributes;
        unset($fillableAttributes['id'], $fillableAttributes['created_at'], $fillableAttributes['updated_at']);

        // 创建基础实例
        $collection = Mockery::mock(Collection::class);
        $collection->exists = true; // 绕过模型事件和钩子

        // 设置基础属性
        $collection->shouldReceive('getAttribute')->with('id')->andReturn($attributes['id'] ?? 1);
        $collection->shouldReceive('getAttribute')->with('source_id')->andReturn($fillableAttributes['source_id'] ?? 1);
        $collection->shouldReceive('getAttribute')->with('source_type')->andReturn($fillableAttributes['source_type'] ?? 'App\\Models\\Content\\Article');
        $collection->shouldReceive('getAttribute')->with('extra')->andReturn($fillableAttributes['extra'] ?? []);
        $collection->shouldReceive('getAttribute')->with('user_id')->andReturn($fillableAttributes['user_id'] ?? 1);
        // 模拟 source 属性
        $collection->shouldReceive('getAttribute')->with('source')->andReturn($source);

        // 处理日期字段
        $createdAt = $hasNullCreatedAt ? null : ($attributes['created_at'] ?? Carbon::now());
        $updatedAt = $hasNullUpdatedAt ? null : ($attributes['updated_at'] ?? Carbon::now());

        if ($createdAt) {
            $collection->shouldReceive('getAttribute')->with('created_at')->andReturn($createdAt);
        } else {
            $collection->shouldReceive('getAttribute')->with('created_at')->andReturn(null);
        }

        if ($updatedAt) {
            $collection->shouldReceive('getAttribute')->with('updated_at')->andReturn($updatedAt);
        } else {
            $collection->shouldReceive('getAttribute')->with('updated_at')->andReturn(null);
        }

        return $collection;
    }

    #[Test]
    #[TestDox('测试字段值映射')]
    public function test_field_value_mapping(): void
    {
        $data = [
            'id' => 123,
            'source_id' => 456,
            'source_type' => 'App\\Models\\Content\\Article',
            'extra' => ['key' => 'value'],
        ];

        // 模拟多态关联
        $source = Mockery::mock('stdClass');
        $source->id = $data['source_id'];

        // 直接在创建时传入source
        $collection = $this->createCollection($data, $source);

        $resource = new CollectionResource($collection);
        $request = Mockery::mock(Request::class);

        $array = $resource->toArray($request);

        $this->assertEquals($data['id'], $array['id']);
        $this->assertEquals($data['source_id'], $array['source_id']);
        $this->assertEquals($data['source_type'], $array['source_type']);
        $this->assertEquals($data['extra'], $array['extra']);
        $this->assertSame($source, $array['source']);
    }

    /**
     * 清理测试资源
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
