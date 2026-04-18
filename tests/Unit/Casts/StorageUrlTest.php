<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\StorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(StorageUrl::class)]
class StorageUrlTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试 get 方法处理空值')]
    public function test_get_method_returns_empty_value_for_empty_input(): void
    {
        $cast = new StorageUrl;
        $model = $this->createMock(Model::class);

        // 测试 null 值
        $result1 = $cast->get($model, 'test', null, []);
        $this->assertNull($result1);

        // 测试空字符串
        $result2 = $cast->get($model, 'test', '', []);
        $this->assertEquals('', $result2);
    }

    #[Test]
    #[TestDox('测试 get 方法处理非空值（集成测试）')]
    public function test_get_method_converts_path_to_url(): void
    {
        $cast = new StorageUrl;
        $model = $this->createMock(Model::class);
        $path = 'path/to/file.jpg';

        $result = $cast->get($model, 'test', $path, []);

        // 验证返回值是字符串
        $this->assertIsString($result);
        // 验证返回值包含原始路径
        $this->assertStringContainsString($path, $result);
    }

    #[Test]
    #[TestDox('测试 set 方法处理值（集成测试）')]
    public function test_set_method_converts_url_to_relative_path(): void
    {
        $cast = new StorageUrl;
        $model = $this->createMock(Model::class);
        $url = 'https://example.com/storage/path/to/file.jpg';

        $result = $cast->set($model, 'test', $url, []);

        // 验证返回值是字符串
        $this->assertIsString($result);
        // 验证返回值不包含完整 URL 前缀
        $this->assertStringNotContainsString('https://example.com', $result);
    }
}
