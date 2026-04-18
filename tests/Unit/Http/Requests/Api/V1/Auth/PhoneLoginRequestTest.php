<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\PhoneLoginRequest;
use App\Rules\PhoneRule;
use App\Rules\SmsCaptchaRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(PhoneLoginRequest::class)]
#[TestDox('手机号登录请求测试')]
class PhoneLoginRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    #[TestDox('测试验证规则')]
    #[DataProvider('validation_rules_data_provider')]
    public function test_validation_rules(array $data, bool $expectedValidity): void
    {
        // 创建请求实例
        $request = new PhoneLoginRequest;
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
    public static function validation_rules_data_provider(): array
    {
        return [
            // 有效的情况
            'valid data' => [
                ['device' => 'mobile', 'phone' => '13812345678', 'verify_code' => '123456'],
                true,
            ],

            // 无效的情况
            'missing device' => [
                ['phone' => '13812345678', 'verify_code' => '123456'],
                false,
            ],
            'missing phone' => [
                ['device' => 'mobile', 'verify_code' => '123456'],
                false,
            ],
            'invalid phone' => [
                ['device' => 'mobile', 'phone' => '12345678', 'verify_code' => '123456'],
                false,
            ],
            'missing verify_code' => [
                ['device' => 'mobile', 'phone' => '13812345678'],
                false,
            ],
            'short verify_code' => [
                ['device' => 'mobile', 'phone' => '13812345678', 'verify_code' => '123'],
                false,
            ],
            'long verify_code' => [
                ['device' => 'mobile', 'phone' => '13812345678', 'verify_code' => '12345678'],
                false,
            ],
        ];
    }

    #[Test]
    #[TestDox('测试验证规则定义')]
    public function test_rules_definition(): void
    {
        $request = new PhoneLoginRequest;
        $rules = $request->rules();

        // 验证规则是否正确定义
        $this->assertArrayHasKey('device', $rules);
        $this->assertContains('required', $rules['device']);
        $this->assertContains('string', $rules['device']);

        $this->assertArrayHasKey('phone', $rules);
        $this->assertContains('required', $rules['phone']);
        $this->assertContainsInstanceOf(PhoneRule::class, $rules['phone']);

        $this->assertArrayHasKey('verify_code', $rules);
        $this->assertContains('required', $rules['verify_code']);
        $this->assertContains('min:4', $rules['verify_code']);
        $this->assertContains('max:6', $rules['verify_code']);
        $this->assertContainsInstanceOf(SmsCaptchaRule::class, $rules['verify_code']);
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
