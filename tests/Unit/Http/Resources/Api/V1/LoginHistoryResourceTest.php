<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\LoginHistoryResource;
use App\Models\User\LoginHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(LoginHistoryResource::class)]
#[TestDox('LoginHistoryResource 测试')]
class LoginHistoryResourceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试资源数组结构是否正确')]
    public function test_resource_structure()
    {
        // 创建请求对象
        $request = new Request;
        // 创建测试数据
        $loginHistory = $this->createLoginHistory();

        // 创建资源实例
        $resource = new LoginHistoryResource($loginHistory);

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证数组结构
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('ip', $result);
        $this->assertArrayHasKey('user_agent', $result);
        $this->assertArrayHasKey('address', $result);
        $this->assertArrayHasKey('browser', $result);
        $this->assertArrayHasKey('login_at', $result);

        // 验证字段数量
        $this->assertCount(6, $result);
    }

    #[Test]
    #[TestDox('测试日期格式是否正确')]
    public function test_date_format()
    {
        // 创建请求对象
        $request = new Request;
        // 创建带特定日期的测试数据
        $loginDate = Carbon::now();
        $loginHistory = $this->createLoginHistory([
            'login_at' => $loginDate,
        ]);

        // 创建资源实例
        $resource = new LoginHistoryResource($loginHistory);

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证日期格式
        $this->assertEquals($loginDate->toDateTimeString(), $result['login_at']);
    }

    #[Test]
    #[TestDox('测试null日期处理')]
    public function test_null_date_handling()
    {
        // 创建请求对象
        $request = new Request;
        // 创建带null日期的测试数据
        $loginHistory = $this->createLoginHistory([
            'login_at' => null,
        ]);

        // 创建资源实例
        $resource = new LoginHistoryResource($loginHistory);

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证null日期处理
        $this->assertNull($result['login_at']);
    }

    #[Test]
    #[TestDox('测试字段值映射是否正确')]
    public function test_field_values()
    {
        // 创建请求对象
        $request = new Request;
        // 创建带特定字段值的测试数据
        $testData = [
            'id' => 123,
            'ip' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'address' => '北京市',
            'browser' => 'Chrome',
        ];

        $loginHistory = $this->createLoginHistory($testData);

        // 创建资源实例
        $resource = new LoginHistoryResource($loginHistory);

        // 获取资源数组
        $result = $resource->toArray($request);

        // 验证字段值映射
        $this->assertEquals($testData['id'], $result['id']);
        $this->assertEquals($testData['ip'], $result['ip']);
        $this->assertEquals($testData['user_agent'], $result['user_agent']);
        $this->assertEquals($testData['address'], $result['address']);
        $this->assertEquals($testData['browser'], $result['browser']);
    }

    /**
     * 创建登录历史测试数据
     */
    private function createLoginHistory(array $data = []): LoginHistory
    {
        // 创建基础数据，不包含id
        $baseData = [
            'user_id' => 1,
            'user_type' => 'App\\Models\\User',
            'ip' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'address' => '本地地址',
            'browser' => 'Test Browser',
            'login_at' => Carbon::now(),
        ];

        // 合并数据
        $mergedData = array_merge($baseData, $data);

        // 提取id（如果存在）
        $id = $mergedData['id'] ?? 1;
        unset($mergedData['id']);

        // 创建LoginHistory实例
        $loginHistory = new LoginHistory($mergedData);

        // 直接设置id属性
        $loginHistory->setAttribute('id', $id);

        // 绕过创建时的事件和钩子
        $loginHistory->exists = true;

        return $loginHistory;
    }
}
