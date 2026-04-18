<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Http\Controllers\Api\V1\RegisterController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(RegisterController::class)]
#[TestDox('注册控制器测试')]
class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    #[TestDox('测试账号检查接口 - 已存在邮箱返回true')]
    public function test_exists_returns_true_for_existing_email()
    {
        $user = User::factory()->email()->create();

        $response = $this->postJson(route('api.v1.register.exists'), [
            'email' => $user->email,
        ]);

        $response->assertOk();
        $response->assertJson(['exists' => true]);
    }

    #[Test]
    #[TestDox('测试账号检查接口 - 已存在手机号返回true')]
    public function test_exists_returns_true_for_existing_phone()
    {
        $user = User::factory()->phone()->create();

        $response = $this->postJson(route('api.v1.register.exists'), [
            'phone' => $user->phone,
        ]);

        $response->assertOk();
        $response->assertJson(['exists' => true]);
    }

    #[Test]
    #[TestDox('测试账号检查接口 - 已存在用户名返回true')]
    public function test_exists_returns_true_for_existing_username()
    {
        // 生成符合 UsernameRule 的用户名
        $username = $this->faker->unique()->userName();
        // 只保留允许的字符（字母、数字、下划线和连字符）
        $username = preg_replace('/[^-a-zA-Z0-9_]/u', '', $username);
        $user = User::factory()->create(['username' => $username]);

        $response = $this->postJson(route('api.v1.register.exists'), [
            'username' => $user->username,
        ]);

        $response->assertOk();
        $response->assertJson(['exists' => true]);
    }

    #[Test]
    #[TestDox('测试账号检查接口 - 不存在的账号返回false')]
    public function test_exists_returns_false_when_account_not_exists()
    {
        $response = $this->postJson(route('api.v1.register.exists'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['exists' => false]);
    }

    #[Test]
    #[TestDox('测试邮箱注册接口 - 成功创建用户')]
    public function test_email_register_creates_user_successfully()
    {
        settings()->set('user.enable_register', '1', 'bool');
        settings()->set('user.enable_email_register', '1', 'bool');
        $username = $this->faker->unique()->userName();
        $username = preg_replace('/[^-a-zA-Z0-9_]/u', '', $username);
        $password = $this->faker->password(12);

        $response = $this->postJson(route('api.v1.register.email'), [
            'device' => 'PC',
            'email' => $username.'test@example.com',
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'access_token',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => $username.'test@example.com',
            'username' => $username,
        ]);

    }

    #[Test]
    #[TestDox('测试邮箱注册接口 - 返回验证错误')]
    public function test_email_register_returns_validation_errors()
    {
        settings()->set('user.enable_register', '1', 'bool');
        settings()->set('user.enable_email_register', '1', 'bool');
        $response = $this->postJson(route('api.v1.register.email'), [
            'device' => 'PC',
            'email' => 'invalid-email',
            'password' => '123',
            'username' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    #[TestDox('测试手机注册接口 - 成功创建用户')]
    public function test_phone_register_creates_user_successfully()
    {
        settings()->set('user.enable_register', '1', 'bool');
        settings()->set('user.enable_phone_register', '1', 'bool');
        $phone = $this->faker->unique()->numerify('13#########');
        $password = $this->faker->password(6, 20);
        $response = $this->postJson('/api/v1/register/phone-register', [
            'device' => 'PC',
            'phone' => $phone,
            'password' => $password,
            'password_confirmation' => $password,
            'verify_code' => '123456',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'access_token',
        ]);
        $this->assertDatabaseHas('users', [
            'phone' => $phone,
        ]);
    }

    #[Test]
    #[TestDox('测试手机注册接口 - 返回验证错误')]
    public function test_phone_register_returns_validation_errors()
    {
        settings()->set('user.enable_register', '1', 'bool');
        settings()->set('user.enable_phone_register', '1', 'bool');
        $response = $this->postJson('/api/v1/register/phone-register', [
            'device' => 'PC',
            'phone' => 'invalid-phone',
            'verify_code' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone', 'verify_code']);
    }

    #[Test]
    #[TestDox('测试注册接口限流功能')]
    public function test_rate_limit_is_applied_for_registration()
    {
        settings()->set('user.register_throttle', '5,1', 'string');
        settings()->set('user.enable_register', '1', 'bool');
        settings()->set('user.enable_phone_register', '1', 'bool');
        $password = $this->faker->password(6, 20);

        // 发送5次正常请求
        for ($i = 0; $i < 5; $i++) {
            $phone = $this->faker->unique()->numerify('13#########');
            $data = [
                'device' => 'PC',
                'phone' => $phone,
                'password' => $password,
                'password_confirmation' => $password,
                'verify_code' => '123456',
            ];
            $response = $this->postJson(route('api.v1.register.phone'), $data);
            $response->assertCreated();
        }

        // 第6次请求应该被限流
        $phone = $this->faker->unique()->numerify('13#########');
        $data = [
            'device' => 'PC',
            'phone' => $phone,
            'password' => $password,
            'password_confirmation' => $password,
            'verify_code' => '123456',
        ];
        $response = $this->postJson(route('api.v1.register.phone'), $data);
        $response->assertStatus(JsonResponse::HTTP_TOO_MANY_REQUESTS);
    }
}
