<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\StatusSwitch;
use App\Enums\UserStatus;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Setting;
use App\Models\User;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台用户管理控制器测试
 */
#[CoversClass(UserController::class)]
#[TestDox('后台用户管理控制器测试')]
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        Permission::findOrCreate('users.index', 'admin');
        Permission::findOrCreate('users.create', 'admin');
        Permission::findOrCreate('users.edit', 'admin');
        Permission::findOrCreate('users.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'users.index', 'users.create', 'users.edit', 'users.delete',
        ]);
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "u_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "u_adm{$suffix}",
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

    /**
     * 生成一个唯一的手机号。
     */
    protected function makePhone(): string
    {
        return '138'.str_pad((string) random_int(1000000, 9999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * 创建一个用户。
     */
    protected function makeUser(array $attributes = []): User
    {
        $phone = $attributes['phone'] ?? $this->makePhone();
        $user = UserHelper::createByPhone($phone, 'password123');
        if (isset($attributes['name'])) {
            $user->update(['name' => $attributes['name']]);
        }

        return $user;
    }

    #[Test]
    #[TestDox('未认证用户访问用户列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/users');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问用户列表返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/users');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取用户列表页面')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $this->makeUser(['name' => '用户A']);
        $this->makeUser(['name' => '用户B']);

        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertViewIs('admin.user.index');
    }

    #[Test]
    #[TestDox('按关键词搜索用户列表页面')]
    public function test_index_search_by_keyword(): void
    {
        $this->actingAsAdmin();
        $this->makeUser(['name' => 'SpecialUser']);
        $this->makeUser(['name' => 'OtherUser']);

        $response = $this->get('/admin/users?keyword=SpecialUser');

        $response->assertOk();
        $response->assertViewIs('admin.user.index');
    }

    #[Test]
    #[TestDox('按ID搜索用户列表页面')]
    public function test_index_search_by_id(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeUser();
        $this->makeUser();

        $response = $this->get('/admin/users?id='.$target->id);

        $response->assertOk();
        $response->assertViewIs('admin.user.index');
    }

    #[Test]
    #[TestDox('创建用户页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/users/create');
        $response->assertOk();
        $response->assertViewIs('admin.user.create');
    }

    #[Test]
    #[TestDox('创建用户成功')]
    public function test_store_creates_user(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/users', [
            'phone' => $this->makePhone(),
            'name' => '新用户',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
    }

    #[Test]
    #[TestDox('创建用户时手机号必填')]
    public function test_store_requires_phone(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/users', [
            'name' => '无手机号用户',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    #[Test]
    #[TestDox('创建用户时手机号不能重复')]
    public function test_store_requires_unique_phone(): void
    {
        $this->actingAsAdmin();
        $existing = $this->makeUser();

        $response = $this->postJson('/admin/users', [
            'phone' => $existing->phone,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    #[Test]
    #[TestDox('编辑用户页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser();

        $response = $this->get('/admin/users/'.$user->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.user.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $user->id);
    }

    #[Test]
    #[TestDox('更新用户信息成功')]
    public function test_update_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser();

        $response = $this->putJson('/admin/users/'.$user->id, [
            'name' => '更新后的名称',
            'phone' => $user->phone,
            'profile' => [
                'intro' => '个人介绍',
                'bio' => '个性签名',
            ],
            'extra' => [
                'invite_code' => $user->extra->invite_code,
                'username_change_count' => $user->extra->username_change_count,
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $user->refresh();
        $this->assertEquals('更新后的名称', $user->name);
    }

    #[Test]
    #[TestDox('更新用户状态成功')]
    public function test_update_status(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser();

        $response = $this->postJson('/admin/users/status', [
            'id' => $user->id,
            'status' => StatusSwitch::DISABLED->value,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
        $user->refresh();
        $this->assertEquals(UserStatus::STATUS_NOT_ACTIVE->value, $user->status->value);
    }

    #[Test]
    #[TestDox('更新用户状态时缺少参数验证失败')]
    public function test_update_status_validation(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/users/status', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id', 'status']);
    }

    #[Test]
    #[TestDox('删除用户成功')]
    public function test_destroy_deletes_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser();

        $response = $this->deleteJson('/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
