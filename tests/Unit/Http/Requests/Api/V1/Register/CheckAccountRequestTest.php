<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\Register;

use App\Http\Requests\Api\V1\Register\CheckAccountRequest;
use App\Rules\PhoneRule;
use App\Rules\UsernameRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(CheckAccountRequest::class)]
#[TestDox('账号校验请求测试')]
class CheckAccountRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试验证规则')]
    #[DataProvider('validationRulesDataProvider')]
    public function test_validation_rules(array $data, bool $expectedValidity): void
    {
        // 创建请求实例
        $request = new CheckAccountRequest;
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
            'only valid username' => [
                ['username' => 'validusername123'],
                true,
            ],
            'only valid email' => [
                ['email' => 'valid.email@example.com'],
                true,
            ],
            'only valid phone' => [
                ['phone' => '13812345678'],
                true,
            ],
            'username and email' => [
                ['username' => 'validusername123', 'email' => 'valid.email@example.com'],
                true,
            ],
            'username and phone' => [
                ['username' => 'validusername123', 'phone' => '13812345678'],
                true,
            ],
            'email and phone' => [
                ['email' => 'valid.email@example.com', 'phone' => '13812345678'],
                true,
            ],
            'all fields' => [
                ['username' => 'validusername123', 'email' => 'valid.email@example.com', 'phone' => '13812345678'],
                true,
            ],

            // 无效的情况
            'no fields' => [
                [],
                false,
            ],
            'invalid username' => [
                ['username' => 'invalid username!'],
                false,
            ],
            'invalid email' => [
                ['email' => 'invalid-email'],
                false,
            ],
            'invalid phone' => [
                ['phone' => '12345678'],
                false,
            ],
        ];
    }

    #[Test]
    #[TestDox('测试验证规则定义')]
    public function test_rules_definition(): void
    {
        $request = new CheckAccountRequest;
        $rules = $request->rules();

        // 验证规则是否正确定义
        $this->assertArrayHasKey('username', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('phone', $rules);

        // 验证username规则
        $this->assertContains('required_without_all:email,phone', $rules['username']);
        $this->assertContainsInstanceOf(UsernameRule::class, $rules['username']);

        // 验证email规则
        $this->assertContains('required_without_all:username,phone', $rules['email']);
        $this->assertContains('email', $rules['email']);

        // 验证phone规则
        $this->assertContains('required_without_all:username,email', $rules['phone']);
        $this->assertContainsInstanceOf(PhoneRule::class, $rules['phone']);
    }

    /**
     * 断言数组中包含指定类型的实例
     */
    protected function assertContainsInstanceOf(string $class, array $array): void
    {
        foreach ($array as $item) {
            if ($item instanceof $class) {
                $this->assertTrue(true);

                return;
            }
        }

        $this->fail("Array does not contain an instance of {$class}");
    }
}
