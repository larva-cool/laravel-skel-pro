<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 后台认证控制器测试
 */
#[CoversClass(AuthController::class)]
#[TestDox('后台认证控制器测试')]
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 清空 settings 表，避免 Setting 模型自动插入的配置影响测试
        Setting::query()->delete();

        $this->admin = $this->makeAdmin();
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "auth_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $username = $attributes['username'] ?? "auth_adm{$suffix}";
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $username, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => $username,
                'name' => '测试管理员',
                'email' => $email,
                'phone' => $phone,
                'password' => 'password123',
                'status' => 1,
            ], $attributes);
            $admin->forceFill($fill);
            $admin->save();

            return $admin;
        });
    }

    /**
     * 以管理员身份登录并禁用 RefreshUserActiveAt 中间件。
     */
    protected function actingAsAdmin(?Admin $admin = null): self
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);

        return $this->actingAs($admin ?? $this->admin, 'admin');
    }

    #[Test]
    #[TestDox('登录页面返回视图')]
    public function test_show_login_form_returns_view(): void
    {
        $response = $this->get('/admin/auth/login');

        $response->assertOk();
        $response->assertViewIs('admin.auth.login');
    }

    #[Test]
    #[TestDox('已登录用户也可以访问登录页')]
    public function test_authenticated_user_can_access_login_form(): void
    {
        $this->actingAsAdmin();

        $response = $this->get('/admin/auth/login');

        $response->assertOk();
        $response->assertViewIs('admin.auth.login');
    }

    #[Test]
    #[TestDox('使用邮箱登录成功')]
    public function test_login_with_email(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => $this->admin->email,
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => __('user.login_success')]);
    }

    #[Test]
    #[TestDox('使用用户名登录成功')]
    public function test_login_with_username(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => $this->admin->username,
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
    }

    #[Test]
    #[TestDox('使用手机号登录成功')]
    public function test_login_with_phone(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => $this->admin->phone,
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
    }

    #[Test]
    #[TestDox('密码错误登录失败')]
    public function test_login_with_wrong_password_fails(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => $this->admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['account']);
    }

    #[Test]
    #[TestDox('登录时账号必填')]
    public function test_login_requires_account(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['account']);
    }

    #[Test]
    #[TestDox('登录时密码必填且至少6位')]
    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/admin/auth/login', [
            'account' => $this->admin->email,
            'password' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    #[Test]
    #[TestDox('退出登录成功')]
    public function test_logout(): void
    {
        // 刷新 admin 实例，确保 remember_token 等数据库字段已加载
        $this->admin->refresh();
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/auth/logout');

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => __('user.logout_successful')]);
    }

    #[Test]
    #[TestDox('未登录用户退出登录被重定向到登录页')]
    public function test_unauthenticated_logout_redirects(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->post('/admin/auth/logout');

        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('停用状态的管理员无法登录')]
    public function test_disabled_admin_cannot_login(): void
    {
        $admin = $this->makeAdmin(['status' => 0]);

        $response = $this->postJson('/admin/auth/login', [
            'account' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['account']);
    }
}
