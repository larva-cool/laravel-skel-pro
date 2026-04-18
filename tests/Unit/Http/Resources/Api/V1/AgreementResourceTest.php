<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Enum\StatusSwitch;
use App\Http\Resources\Api\V1\AgreementResource;
use App\Models\Agreement\Agreement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * AgreementResource 测试类
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[CoversClass(AgreementResource::class)]
#[TestDox('AgreementResource 测试')]
class AgreementResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试资源数组结构是否正确')]
    public function test_resource_structure()
    {
        // 创建测试数据
        $agreement = $this->createAgreement();

        // 创建资源实例
        $resource = new AgreementResource($agreement);

        // 创建模拟请求对象
        $request = new Request;

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证数组结构
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('is_agree', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);

        // 验证字段数量
        $this->assertCount(6, $result);
    }

    #[Test]
    #[TestDox('测试日期格式是否正确')]
    public function test_date_format()
    {
        // 创建带特定日期的测试数据
        $createdAt = Carbon::now();
        $updatedAt = Carbon::now()->addHours(1);
        $agreement = $this->createAgreement([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        // 创建资源实例
        $resource = new AgreementResource($agreement);

        // 创建模拟请求对象
        $request = new Request;

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证日期格式
        $this->assertEquals($createdAt->toDateTimeString(), $result['created_at']);
        $this->assertEquals($updatedAt->toDateTimeString(), $result['updated_at']);
    }

    #[Test]
    #[TestDox('测试null日期处理是否正确')]
    public function test_null_date_handling()
    {
        // 创建测试数据
        $agreement = $this->createAgreement([
            'created_at' => null,
            'updated_at' => null,
        ]);

        // 创建资源实例
        $resource = new AgreementResource($agreement);

        // 创建模拟请求对象
        $request = new Request;

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证null日期处理
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    #[TestDox('测试字段值映射是否正确')]
    public function test_field_values()
    {
        // 创建带特定字段值的测试数据
        $testData = [
            'id' => 123,
            'title' => '用户协议',
            'content' => '这是用户协议的详细内容',
        ];

        $agreement = $this->createAgreement($testData);

        // 创建资源实例
        $resource = new AgreementResource($agreement);

        // 创建模拟请求对象
        $request = new Request;

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证字段值映射
        $this->assertEquals($testData['id'], $result['id']);
        $this->assertEquals($testData['title'], $result['title']);
        $this->assertEquals($testData['content'], $result['content']);
    }

    /**
     * 创建协议测试数据
     */
    private function createAgreement(array $data = []): Agreement
    {
        // 创建基础数据，不包含id和日期字段
        $baseData = [
            'type' => 'user',
            'title' => '测试协议',
            'content' => '测试内容',
            'status' => StatusSwitch::ENABLED->value,
            'order' => 1,
            'admin_id' => 1,
        ];

        // 合并数据
        $mergedData = array_merge($baseData, $data);

        // 提取id（如果存在）
        $id = $mergedData['id'] ?? 1;
        unset($mergedData['id']);

        // 检查是否明确传入了null日期
        $hasNullCreatedAt = array_key_exists('created_at', $data) && $data['created_at'] === null;
        $hasNullUpdatedAt = array_key_exists('updated_at', $data) && $data['updated_at'] === null;

        // 提取created_at（如果存在且不为null，否则使用当前时间）
        $createdAt = $hasNullCreatedAt ? null : ($mergedData['created_at'] ?? Carbon::now());
        unset($mergedData['created_at']);

        // 提取updated_at（如果存在且不为null，否则使用当前时间）
        $updatedAt = $hasNullUpdatedAt ? null : ($mergedData['updated_at'] ?? Carbon::now());
        unset($mergedData['updated_at']);

        // 创建Agreement实例
        $agreement = new Agreement($mergedData);

        // 直接设置id属性
        $agreement->setAttribute('id', $id);

        // 直接设置日期属性
        $agreement->setAttribute('created_at', $createdAt);
        $agreement->setAttribute('updated_at', $updatedAt);

        // 绕过创建时的事件和钩子
        $agreement->exists = true;

        return $agreement;
    }
}
