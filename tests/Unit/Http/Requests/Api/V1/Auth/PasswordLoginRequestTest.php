<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\PasswordLoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(PasswordLoginRequest::class)]
#[TestDox('密码登录请求测试')]
class PasswordLoginRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    #[TestDox('测试验证规则')]
    #[DataProvider('validationRulesDataProvider')]
    public function test_validation_rules(array $data, bool $expectedValidity): void
    {
        // 创建请求实例
        $request = new PasswordLoginRequest;
        $request->merge($data);

        // 验证数据
        $validator = $this->app['validator']->make($data, $request->rules());
        $isValid = $validator->passes();

        // 断言结果
        $this->assertEquals($expectedValidity, $isValid);
    }

    /**
     * 提供测试数据
     */
    #[TestDox('测试验证规则数据提供器')]
    public static function validationRulesDataProvider(): array
    {
        return [
            // 有效的情况
            'valid data' => [
                ['device' => 'mobile', 'account' => 'testuser', 'password' => 'password123'],
                true,
            ],

            // 无效的情况
            'missing device' => [
                ['account' => 'testuser', 'password' => 'password123'],
                false,
            ],
            'missing account' => [
                ['device' => 'mobile', 'password' => 'password123'],
                false,
            ],
            'missing password' => [
                ['device' => 'mobile', 'account' => 'testuser'],
                false,
            ],
            'short password' => [
                ['device' => 'mobile', 'account' => 'testuser', 'password' => '123'],
                false,
            ],
        ];
    }

    #[Test]
    #[TestDox('测试验证规则定义')]
    public function test_rules_definition(): void
    {
        $request = new PasswordLoginRequest;
        $rules = $request->rules();

        // 验证规则是否正确定义
        $this->assertArrayHasKey('device', $rules);
        $this->assertContains('required', $rules['device']);
        $this->assertContains('string', $rules['device']);

        $this->assertArrayHasKey('account', $rules);
        $this->assertContains('required', $rules['account']);
        $this->assertContains('string', $rules['account']);

        $this->assertArrayHasKey('password', $rules);
        $this->assertContains('required', $rules['password']);
        $this->assertContains('string', $rules['password']);
        $this->assertContains('min:6', $rules['password']);
    }
}
