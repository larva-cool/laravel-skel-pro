<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\AsJson;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * AsJson cast 测试
 */
#[TestDox('AsJson cast 测试')]
class AsJsonTest extends TestCase
{
    /**
     * 测试 get 方法
     */
    #[Test]
    #[TestDox('测试 get 方法')]
    public function test_get_method()
    {
        // 创建 AsJson 实例
        $asJson = new AsJson;

        // 创建模拟模型
        $model = $this->createMock(Model::class);

        // 测试场景 1: 非空 JSON 字符串
        $jsonString = '{"name": "Test", "value": 123}';
        $result = $asJson->get($model, 'data', $jsonString, ['data' => $jsonString]);
        $this->assertIsArray($result);
        $this->assertEquals(['name' => 'Test', 'value' => 123], $result);

        // 测试场景 2: 空字符串
        $result = $asJson->get($model, 'data', '', ['data' => '']);
        $this->assertIsArray($result);
        $this->assertEquals([], $result);

        // 测试场景 3: null 值
        $result = $asJson->get($model, 'data', null, ['data' => null]);
        $this->assertIsArray($result);
        $this->assertEquals([], $result);

        // 测试场景 4: 布尔值 false
        $result = $asJson->get($model, 'data', false, ['data' => false]);
        $this->assertIsArray($result);
        $this->assertEquals([], $result);
    }

    /**
     * 测试 set 方法
     */
    #[Test]
    #[TestDox('测试 set 方法')]
    public function test_set_method()
    {
        // 创建 AsJson 实例
        $asJson = new AsJson;

        // 创建模拟模型
        $model = $this->createMock(Model::class);

        // 测试场景 1: 非空数组
        $array = ['name' => 'Test', 'value' => 123];
        $result = $asJson->set($model, 'data', $array, ['data' => $array]);
        $this->assertIsString($result);
        $this->assertJson($result);
        $this->assertEquals('{"name":"Test","value":123}', $result);

        // 测试场景 2: 空数组
        $result = $asJson->set($model, 'data', [], ['data' => []]);
        $this->assertNull($result);

        // 测试场景 3: null 值
        $result = $asJson->set($model, 'data', null, ['data' => null]);
        $this->assertNull($result);

        // 测试场景 4: 可转换为数组的值
        $object = (object) ['name' => 'Test', 'value' => 123];
        $result = $asJson->set($model, 'data', $object, ['data' => $object]);
        $this->assertIsString($result);
        $this->assertJson($result);

        // 测试场景 5: 不可 JSON 编码的值（应捕获异常并返回 null）
        // 创建一个循环引用的数组，这会导致 JSON 编码失败
        $circularArray = [];
        $circularArray['self'] = &$circularArray;
        $result = $asJson->set($model, 'data', $circularArray, ['data' => $circularArray]);
        $this->assertNull($result);
    }
}
