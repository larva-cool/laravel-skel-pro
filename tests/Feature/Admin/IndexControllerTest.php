<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\IndexController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 后台首页控制器测试
 */
#[CoversClass(IndexController::class)]
#[TestDox('后台首页控制器测试')]
class IndexControllerTest extends TestCase
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

        $email = $attributes['email'] ?? "idx_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "idx_adm{$suffix}",
                'name' => '测试管理员'.$suffix,
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
    #[TestDox('未认证用户访问首页被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('首页返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin');
        $response->assertOk();
        $response->assertViewIs('admin.index.index');
    }

    #[Test]
    #[TestDox('通过 index 路由访问首页返回视图')]
    public function test_index_route_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/index');
        $response->assertOk();
        $response->assertViewIs('admin.index.index');
    }

    #[Test]
    #[TestDox('仪表盘页面返回视图')]
    public function test_dashboard_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewIs('admin.index.dashboard');
        $response->assertViewHasAll([
            'today_user_count',
            'day7_user_count',
            'day30_user_count',
            'user_count',
            'laravel_version',
            'laravel_environment',
            'mysql_version',
            'php_version',
            'os',
            'day30_detail',
        ]);
    }

    #[Test]
    #[TestDox('获取后台配置JSON')]
    public function test_config_returns_json(): void
    {
        $this->actingAsAdmin();

        // 创建一条 /admin/dashboard 菜单，使 config 能找到 dashboard 配置
        AdminMenu::create([
            'title' => '仪表盘',
            'href' => '/admin/dashboard',
            'type' => 1,
            'order' => 0,
        ]);

        $response = $this->getJson('/admin/config');

        $response->assertOk();
        $response->assertJsonStructure([
            'logo',
            'menu',
            'tab',
        ]);
    }

    #[Test]
    #[TestDox('获取当前管理员账户信息')]
    public function test_account_returns_admin_resource(): void
    {
        // 刷新 admin 实例，确保所有数据库字段已加载
        $this->admin->refresh();
        $this->actingAsAdmin();

        $response = $this->getJson('/admin/account');

        $response->assertOk();
        $response->assertJsonStructure([
            'id', 'username', 'name', 'email',
        ]);
        $this->assertEquals($this->admin->id, $response->json('id'));
    }
}
