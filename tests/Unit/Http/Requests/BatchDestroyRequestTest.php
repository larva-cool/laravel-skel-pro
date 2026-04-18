<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\BatchDestroyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 批量删除请求测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(BatchDestroyRequest::class)]
class BatchDestroyRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试未登录用户授权失败
     */
    #[Test]
    #[TestDox('测试未登录用户授权失败')]
    public function test_unauthorized_for_guest_user(): void
    {
        // 确保用户未登录
        Auth::logout();

        // 创建请求实例
        $request = BatchDestroyRequest::create('/batch-destroy', 'POST', [
            'ids' => [1, 2, 3],
        ]);

        // 手动设置容器实例和用户解析器（返回null表示未登录）
        $request->setContainer($this->app);
        $request->setUserResolver(function () {
            return null;
        });

        // 测试授权失败
        $this->assertFalse($request->authorize());
    }

    /**
     * 测试已登录用户授权成功
     */
    #[Test]
    #[TestDox('测试已登录用户授权成功')]
    public function test_authorized_for_authenticated_user(): void
    {
        // 创建并登录用户
        $user = User::create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'name' => 'Test User',
        ]);

        Auth::login($user);

        // 创建请求实例
        $request = BatchDestroyRequest::create('/batch-destroy', 'POST', [
            'ids' => [1, 2, 3],
        ]);

        // 手动设置容器实例和用户
        $request->setContainer($this->app);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // 测试授权成功
        $this->assertTrue($request->authorize());
    }

    /**
     * 测试验证规则
     */
    #[Test]
    #[TestDox('测试验证规则')]
    public function test_validation_rules(): void
    {
        // 创建请求实例
        $request = BatchDestroyRequest::create('/batch-destroy', 'POST', [
            'ids' => [1, 2, 3],
        ]);

        // 获取验证规则
        $rules = $request->rules();

        // 测试规则是否正确
        $this->assertArrayHasKey('ids', $rules);
        $this->assertEquals(['required', 'array'], $rules['ids']);
    }

    /**
     * 测试缺少 ids 字段时验证失败
     */
    #[Test]
    #[TestDox('测试缺少 ids 字段时验证失败')]
    public function test_validation_fails_when_ids_field_is_missing(): void
    {
        // 创建请求实例
        $request = BatchDestroyRequest::create('/batch-destroy', 'POST', []);

        // 获取验证规则
        $rules = $request->rules();

        // 创建验证器
        $validator = Validator::make([], $rules);

        // 测试验证失败
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ids', $validator->errors()->messages());
    }

    /**
     * 测试 ids 字段不是数组时验证失败
     */
    #[Test]
    #[TestDox('测试 ids 字段不是数组时验证失败')]
    public function test_validation_fails_when_ids_field_is_not_array(): void
    {
        // 创建请求实例
        $request = BatchDestroyRequest::create('/batch-destroy', 'POST', [
            'ids' => '1,2,3',
        ]);

        // 获取验证规则
        $rules = $request->rules();

        // 创建验证器
        $validator = Validator::make(['ids' => '1,2,3'], $rules);

        // 测试验证失败
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ids', $validator->errors()->messages());
    }

    /**
     * 测试 ids 字段是数组时验证成功
     */
    #[Test]
    #[TestDox('测试 ids 字段是数组时验证成功')]
    public function test_validation_passes_when_ids_field_is_array(): void
    {
        // 创建请求实例
        $request = BatchDestroyRequest::create('/batch-destroy', 'POST', [
            'ids' => [1, 2, 3],
        ]);

        // 获取验证规则
        $rules = $request->rules();

        // 创建验证器
        $validator = Validator::make(['ids' => [1, 2, 3]], $rules);

        // 测试验证成功
        $this->assertTrue($validator->passes());
        $this->assertEmpty($validator->errors()->messages());
    }

    /**
     * 测试 ids 字段是空数组时验证失败
     */
    #[Test]
    #[TestDox('测试 ids 字段是空数组时验证失败')]
    public function test_validation_fails_when_ids_field_is_empty_array(): void
    {
        // 创建请求实例
        $request = BatchDestroyRequest::create('/batch-destroy', 'POST', [
            'ids' => [],
        ]);

        // 获取验证规则
        $rules = $request->rules();

        // 创建验证器
        $validator = Validator::make(['ids' => []], $rules);

        // 测试验证失败（因为 required 规则会拒绝空数组）
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ids', $validator->errors()->messages());
    }
}
