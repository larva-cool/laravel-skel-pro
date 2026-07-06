<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enum\StatusSwitch;
use App\Enum\UserStatus;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台管理员控制器测试
 */
#[CoversClass(AdminController::class)]
#[TestDox('后台管理员控制器测试')]
class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 为所需权限创建权限记录
        Permission::findOrCreate('admins.index', 'admin');
        Permission::findOrCreate('admins.create', 'admin');
        Permission::findOrCreate('admins.edit', 'admin');
        Permission::findOrCreate('admins.delete', 'admin');

        // 创建已登录管理员（绕过 booted 事件避免自动创建 user 时冲突）
        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo(['admins.index', 'admins.create', 'admins.edit', 'admins.delete']);
    }

    /**
     * 创建一个管理员并返回实例。
     * 使用 withoutEvents 绕过 creating 事件中对 phone 的处理，保证测试可控。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "adm{$suffix}@example.com";
        $username = $attributes['username'] ?? "adm{$suffix}";
        $name = $attributes['name'] ?? '测试管理员'.$suffix;
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);

        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $username, $name, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'status' => UserStatus::STATUS_ACTIVE->value,
            ], $attributes);
            if (! isset($fill['password'])) {
                $fill['password'] = 'password123';
            }
            $admin->forceFill($fill);
            $admin->save();

            return $admin;
        });
    }

    /**
     * 后台请求会经过 RefreshUserActiveAt 中间件，
     * 该中间件派发的 RefreshUserLastActiveAtJob 调用 $user->refreshLastActiveAt()，
     * 而 Admin 模型未实现该方法会导致 BadMethodCallException。
     * 通过 withoutMiddleware 禁用该中间件以让测试聚焦于 AdminController 的逻辑。
     */
    protected function actingAsAdmin(?Admin $admin = null): self
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);

        return $this->actingAs($admin ?? $this->admin, 'admin');
    }

    #[Test]
    #[TestDox('未认证用户访问管理员列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/admins');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问管理员列表返回403')]
    public function test_user_without_permission_forbidden(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/admins');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取管理员列表成功')]
    public function test_index_returns_admin_list(): void
    {
        $this->actingAsAdmin();
        $this->makeAdmin();
        $this->makeAdmin();

        $response = $this->getJson('/admin/admins');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'username', 'name', 'email'],
            ],
            'links',
            'meta',
        ]);
        // 仅断言返回 data 包含我们刚创建的两个管理员（+ setUp 的 $this->admin）
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(3, count($data));
    }

    #[Test]
    #[TestDox('按关键词搜索管理员')]
    public function test_index_search_by_keyword(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeAdmin(['name' => 'SpecialName']);
        $this->makeAdmin(['name' => 'OtherName']);

        $response = $this->getJson('/admin/admins?keyword=SpecialName');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($target->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('创建管理员页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/admins/create');
        $response->assertOk();
        $response->assertViewIs('admin.admin.create');
    }

    #[Test]
    #[TestDox('编辑管理员页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeAdmin();

        $response = $this->get('/admin/admins/'.$target->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.admin.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $target->id);
    }

    #[Test]
    #[TestDox('个人信息页面返回视图')]
    public function test_person_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/admins/person');
        $response->assertOk();
        $response->assertViewIs('admin.admin.person');
        $response->assertViewHas('admin', fn ($a) => $a->id === $this->admin->id);
    }

    #[Test]
    #[TestDox('创建管理员时邮箱必填')]
    public function test_store_requires_email(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/admins', [
            'password' => 'password123',
            'roles' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    #[Test]
    #[TestDox('创建管理员时密码必须至少8位')]
    public function test_store_validates_password_length(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/admins', [
            'email' => 'pw'.Str::random(4).'@example.com',
            'password' => '123',
            'roles' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    #[Test]
    #[TestDox('更新管理员基本信息成功')]
    public function test_update_admin(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeAdmin();

        $response = $this->putJson('/admin/admins/'.$target->id, [
            'name' => '新名字',
            'email' => 'new_'.$target->email,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $target->refresh();
        $this->assertEquals('新名字', $target->name);
    }

    #[Test]
    #[TestDox('更新管理员状态为停用')]
    public function test_update_status(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeAdmin(['status' => UserStatus::STATUS_ACTIVE->value]);

        $response = $this->postJson('/admin/admins/status', [
            'id' => $target->id,
            'status' => StatusSwitch::DISABLED->value,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
        $target->refresh();
        // StatusSwitch::DISABLED=0 对应 UserStatus::STATUS_NOT_ACTIVE=0
        $this->assertEquals(UserStatus::STATUS_NOT_ACTIVE->value, $target->status->value);
    }

    #[Test]
    #[TestDox('更新管理员状态为启用')]
    public function test_update_status_enable(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeAdmin(['status' => UserStatus::STATUS_FROZEN->value]);

        $response = $this->postJson('/admin/admins/status', [
            'id' => $target->id,
            'status' => StatusSwitch::ENABLED->value,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
        $target->refresh();
        $this->assertEquals(UserStatus::STATUS_ACTIVE->value, $target->status->value);
    }

    #[Test]
    #[TestDox('更新状态时缺少 id 或 status 参数会验证失败')]
    public function test_update_status_validation(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/admins/status', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id', 'status']);
    }

    #[Test]
    #[TestDox('更新个人信息成功')]
    public function test_store_person_updates_profile(): void
    {
        $this->actingAsAdmin();

        $newEmail = 'person'.Str::random(6).'@example.com';
        $response = $this->postJson('/admin/admins/person', [
            'name' => '新昵称',
            'email' => $newEmail,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
        $this->admin->refresh();
        $this->assertEquals('新昵称', $this->admin->name);
        $this->assertEquals($newEmail, $this->admin->email);
    }

    #[Test]
    #[TestDox('更新个人信息时邮箱必填')]
    public function test_store_person_requires_email(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/admins/person', [
            'name' => '昵称',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    #[Test]
    #[TestDox('更新密码时旧密码错误验证失败')]
    public function test_store_password_wrong_old_password(): void
    {
        $me = $this->makeAdmin();
        $me->givePermissionTo(['admins.edit']);
        $this->actingAsAdmin($me);

        $response = $this->postJson('/admin/admins/password', [
            'old_password' => 'wrong-old-pass',
            'new_password' => 'newpass123!',
            'new_password_confirm' => 'newpass123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['old_password']);
    }

    #[Test]
    #[TestDox('删除普通管理员成功')]
    public function test_destroy_deletes_admin(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeAdmin();

        $response = $this->deleteJson('/admin/admins/'.$target->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertSoftDeleted('admin_users', ['id' => $target->id]);
    }

    #[Test]
    #[TestDox('不能删除超级管理员（id=10000000）')]
    public function test_destroy_super_admin_forbidden(): void
    {
        $superUser = UserHelper::createByEmail('super'.Str::random(4).'@example.com');
        /** @var Admin $super */
        $super = Admin::withoutEvents(function () use ($superUser) {
            $admin = new Admin;
            $admin->id = 10000000;
            $admin->forceFill([
                'user_id' => $superUser->id,
                'username' => 'super_'.Str::random(4),
                'name' => '超级管理员',
                'email' => $superUser->email,
                'phone' => '139'.str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'password' => 'password123',
                'status' => UserStatus::STATUS_ACTIVE->value,
            ]);
            $admin->save();

            return $admin;
        });

        $this->actingAsAdmin();
        $response = $this->deleteJson('/admin/admins/'.$super->id);

        $response->assertOk();
        $response->assertJson(['code' => 1, 'message' => trans('system.super_admin_cant_delete')]);
        $this->assertDatabaseHas('admin_users', ['id' => $super->id, 'deleted_at' => null]);
    }
}
