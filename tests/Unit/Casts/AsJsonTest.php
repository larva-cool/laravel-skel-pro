<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\AsJson;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * AsJson 自定义转换器单元测试
 */
#[CoversClass(AsJson::class)]
#[Group('casts')]
class AsJsonTest extends TestCase
{
    private AsJson $caster;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->caster = new AsJson;
        $this->model = $this->createMock(Model::class);
    }

    /**
     * 测试 get 方法解码 JSON 字符串为数组
     */
    #[Test]
    #[TestDox('get 方法将 JSON 字符串解码为关联数组')]
    public function get_decodes_json_string_to_array(): void
    {
        $json = '{"name":"test","value":123}';

        $result = $this->caster->get($this->model, 'data', $json, []);

        $this->assertIsArray($result);
        $this->assertSame('test', $result['name']);
        $this->assertSame(123, $result['value']);
    }

    /**
     * 测试 get 方法对 null 值返回空数组
     */
    #[Test]
    #[TestDox('get 方法对 null 值返回空数组')]
    public function get_returns_empty_array_for_null_value(): void
    {
        $result = $this->caster->get($this->model, 'data', null, []);

        $this->assertSame([], $result);
    }

    /**
     * 测试 get 方法对空字符串返回空数组
     */
    #[Test]
    #[TestDox('get 方法对空字符串返回空数组')]
    public function get_returns_empty_array_for_empty_string(): void
    {
        $result = $this->caster->get($this->model, 'data', '', []);

        $this->assertSame([], $result);
    }

    /**
     * 测试 set 方法将数组编码为 JSON 字符串
     */
    #[Test]
    #[TestDox('set 方法将关联数组编码为 JSON 字符串')]
    public function set_encodes_array_to_json_string(): void
    {
        $data = ['name' => 'test', 'value' => 123];

        $result = $this->caster->set($this->model, 'data', $data, []);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertSame('test', $decoded['name']);
        $this->assertSame(123, $decoded['value']);
    }

    /**
     * 测试 set 方法将非数组值转换为数组后编码
     */
    #[Test]
    #[TestDox('set 方法将标量值转换为数组后编码')]
    public function set_converts_scalar_to_array_before_encoding(): void
    {
        $result = $this->caster->set($this->model, 'data', 'hello', []);

        $this->assertIsString($result);
        $this->assertSame(['hello'], json_decode($result, true));
    }

    /**
     * 测试 set 方法对空数组返回 null
     */
    #[Test]
    #[TestDox('set 方法对空数组返回 null')]
    public function set_returns_null_for_empty_array(): void
    {
        $result = $this->caster->set($this->model, 'data', [], []);

        $this->assertNull($result);
    }

    /**
     * 测试 set 方法对 null 值返回 null
     */
    #[Test]
    #[TestDox('set 方法对 null 值返回 null')]
    public function set_returns_null_for_null_value(): void
    {
        $result = $this->caster->set($this->model, 'data', null, []);

        $this->assertNull($result);
    }

    /**
     * 测试 set 方法对空字符串转换为数组后返回 null
     */
    #[Test]
    #[TestDox('set 方法对空字符串转换为空数组后返回 null')]
    public function set_returns_null_for_empty_string(): void
    {
        $result = $this->caster->set($this->model, 'data', '', []);

        $this->assertNull($result);
    }

    /**
     * 测试 set 方法编码嵌套数组
     */
    #[Test]
    #[TestDox('set 方法正确编码嵌套数组')]
    public function set_encodes_nested_array(): void
    {
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep_value',
                ],
            ],
        ];

        $result = $this->caster->set($this->model, 'data', $data, []);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertSame('deep_value', $decoded['level1']['level2']['level3']);
    }

    /**
     * 测试 get 与 set 的往返一致性
     */
    #[Test]
    #[TestDox('get 与 set 的往返操作保持数据一致')]
    public function get_and_set_are_round_trip_consistent(): void
    {
        $original = ['key' => 'value', 'nested' => ['a' => 1, 'b' => 2]];

        $encoded = $this->caster->set($this->model, 'data', $original, []);
        $decoded = $this->caster->get($this->model, 'data', $encoded, []);

        $this->assertSame($original, $decoded);
    }
}
