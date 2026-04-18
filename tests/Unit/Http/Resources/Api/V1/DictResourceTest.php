<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\DictResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 字典资源测试
 */
#[CoversClass(DictResource::class)]
class DictResourceTest extends TestCase
{
    /**
     * 测试资源转换为数组
     */
    #[Test]
    #[TestDox('测试资源转换为数组')]
    public function test_to_array()
    {
        // 创建测试数据
        $testData = [
            'name' => '测试字典',
            'value' => 'test_value',
            'extra_field' => 'should_not_be_included',
        ];

        // 创建资源实例
        $resource = new DictResource($testData);

        // 转换资源为数组
        $result = $resource->toArray(null);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertEquals($testData['name'], $result['name']);
        $this->assertEquals($testData['value'], $result['value']);
        $this->assertArrayNotHasKey('extra_field', $result);
    }

    /**
     * 测试资源集合
     */
    #[Test]
    #[TestDox('测试资源集合')]
    public function test_collection()
    {
        // 创建测试数据集合
        $testData = [
            ['name' => '字典1', 'value' => 'value1'],
            ['name' => '字典2', 'value' => 'value2'],
        ];

        // 创建资源集合
        $collection = DictResource::collection($testData);

        // 创建请求对象
        $request = new Request;

        // 转换集合为数组
        $result = $collection->toArray($request);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // 验证第一个元素
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertEquals($testData[0]['name'], $result[0]['name']);
        $this->assertEquals($testData[0]['value'], $result[0]['value']);

        // 验证第二个元素
        $this->assertArrayHasKey('name', $result[1]);
        $this->assertArrayHasKey('value', $result[1]);
        $this->assertEquals($testData[1]['name'], $result[1]['name']);
        $this->assertEquals($testData[1]['value'], $result[1]['value']);
    }
}
