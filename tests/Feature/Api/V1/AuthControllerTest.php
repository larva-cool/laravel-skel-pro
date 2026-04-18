<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Http\Controllers\Api\V1\AuthController;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\SmsCaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(AuthController::class)]
#[TestDox('认证控制器测试')]
class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 测试前置方法
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    #[TestDox('测试用户可以成功登录')]
    public function test_user_can_login_successfully()
    {
        $user = User::factory()->email()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'account' => $user->email,
            'password' => 'password123',
            'device' => 'PC',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
            ]);
    }

    #[Test]
    #[TestDox('测试用户使用无效凭据无法登录')]
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->email()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'account' => $user->email,
            'password' => 'wrongpassword',
            'device' => 'PC',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => '用户名或密码错误。',
            ]);
    }

    #[Test]
    #[TestDox('测试用户可以登出')]
    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->delete('/api/v1/auth/tokens');

        $response->assertStatus(204);
    }

    #[Test]
    #[TestDox('测试用户可以刷新令牌')]
    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/refresh-token', [
                'device' => 'PC',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
            ]);
    }

    #[Test]
    #[TestDox('测试用户可以通过手机号成功登录')]
    public function test_user_can_login_with_phone_successfully()
    {
        settings()->set('user.enable_phone_login', '1', 'bool');
        $phone = $this->faker->unique()->numerify('13#########');
        $user = User::factory()->create(['phone' => $phone]);
        $code = '123456';

        // 模拟验证码验证通过
        $this->mock(SmsCaptchaService::class, function ($mock) use ($user, $code) {
            $mock->shouldReceive('verify')->with($user->phone, $code)->andReturn(true);
        });

        $response = $this->postJson('/api/v1/auth/phone-login', [
            'phone' => $user->phone,
            'verify_code' => $code,
            'device' => 'test_device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'token_id',
            'expires_in',
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test_device',
        ]);
    }

    #[Test]
    #[TestDox('测试用户使用无效的手机验证码无法登录')]
    public function test_user_cannot_login_with_invalid_phone_verification_code()
    {
        $phone = $this->faker->unique()->numerify('13#########');
        $user = User::factory()->create(['phone' => $phone]);
        $invalidCode = '654321';

        // 模拟验证码验证失败
        $this->mock(SmsCaptchaService::class, function ($mock) use ($user, $invalidCode) {
            $mock->shouldReceive('verify')->with($user->phone, $invalidCode)->andReturn(false);
        });

        $response = $this->postJson('/api/v1/auth/phone-login', [
            'phone' => $user->phone,
            'verify_code' => $invalidCode,
            'device' => 'test_device',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token_id' => $user->id,
            'name' => 'test_device',
        ]);
    }

    #[Test]
    #[TestDox('测试用户使用不存在的手机号无法登录')]
    public function test_user_cannot_login_with_nonexistent_phone()
    {
        $nonexistentPhone = '13900139000';
        $code = '1234561';

        $response = $this->postJson('/api/v1/auth/phone-login', [
            'phone' => $nonexistentPhone,
            'verify_code' => $code,
            'device' => 'test_device',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['verify_code']);
    }

    #[Test]
    #[TestDox('测试用户可以通过手机号成功重置密码')]
    public function test_user_can_reset_password_with_phone_successfully()
    {
        $phone = $this->faker->unique()->numerify('13#########');
        $user = User::factory()->create(['phone' => $phone]);
        $code = '123456';
        $newPassword = 'NewPass@@@@word123!';

        // 模拟验证码验证通过
        $this->mock(SmsCaptchaService::class, function ($mock) use ($user, $code) {
            $mock->shouldReceive('verify')->with($user->phone, $code)->andReturn(true);
        });

        $response = $this->postJson('/api/v1/auth/phone-reset-password', [
            'phone' => $user->phone,
            'verify_code' => $code,
            'password' => $newPassword,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => __('user.password_reset_complete'),
        ]);
        // 验证密码已更新
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    #[Test]
    #[TestDox('测试用户使用无效的验证码无法重置密码')]
    public function test_user_cannot_reset_password_with_invalid_verification_code()
    {
        $phone = $this->faker->unique()->numerify('13#########');
        $user = User::factory()->create(['phone' => $phone]);
        $invalidCode = '654321';
        $newPassword = 'NewPass@@@@word123!';

        // 模拟验证码验证失败
        $this->mock(SmsCaptchaService::class, function ($mock) use ($user, $invalidCode) {
            $mock->shouldReceive('verify')->with($user->phone, $invalidCode)->andReturn(false);
        });

        $response = $this->postJson('/api/v1/auth/phone-reset-password', [
            'phone' => $user->phone,
            'verify_code' => $invalidCode,
            'password' => $newPassword,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['verify_code']);
        // 验证密码未更新
        $this->assertFalse(Hash::check($newPassword, $user->fresh()->password));
    }

    #[Test]
    #[TestDox('测试用户使用不存在的手机号无法重置密码')]
    public function test_user_cannot_reset_password_with_nonexistent_phone()
    {
        $nonexistentPhone = '13900139000';
        $code = '123456';
        $newPassword = 'NewPass@@@word123!';

        $response = $this->postJson('/api/v1/auth/phone-reset-password', [
            'phone' => $nonexistentPhone,
            'verify_code' => $code,
            'password' => $newPassword,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    #[Test]
    #[TestDox('测试用户使用无效的密码格式无法重置密码')]
    public function test_user_cannot_reset_password_with_invalid_password()
    {
        $phone = $this->faker->unique()->numerify('13#########');
        $user = User::factory()->create(['phone' => $phone]); // 使用唯一电话号码
        $code = '123456';
        $invalidPassword = '123'; // 密码太短，不符合验证规则

        // 模拟验证码验证通过
        $this->mock(SmsCaptchaService::class, function ($mock) use ($user, $code) {
            $mock->shouldReceive('verify')->with($user->phone, $code)->andReturn(true);
        });

        $response = $this->postJson('/api/v1/auth/phone-reset-password', [
            'phone' => $user->phone,
            'verify_code' => $code,
            'password' => $invalidPassword,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        // 验证密码未更新
        $this->assertFalse(Hash::check($invalidPassword, $user->fresh()->password));
    }

    #[Test]
    #[TestDox('测试用户可以成功销毁令牌')]
    public function test_user_can_destroy_token_successfully()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $tokenId = PersonalAccessToken::where('tokenable_id', $user->id)->first()->id;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->deleteJson("/api/v1/auth/tokens/{$tokenId}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
            'tokenable_id' => $user->id,
        ]);
    }

    #[Test]
    #[TestDox('测试用户无法销毁不存在的令牌')]
    public function test_user_cannot_destroy_nonexistent_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $nonexistentTokenId = 999999;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->deleteJson("/api/v1/auth/tokens/{$nonexistentTokenId}");

        $response->assertStatus(404);
    }

    #[Test]
    #[TestDox('测试未认证用户无法销毁令牌')]
    public function test_unauthenticated_user_cannot_destroy_token()
    {
        $response = $this->deleteJson('/api/v1/auth/tokens/1');

        $response->assertStatus(401);
    }
}
