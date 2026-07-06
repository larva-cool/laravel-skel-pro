<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\RoleController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Permission;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * 后台角色控制器测试
 */
#[CoversClass(RoleController::class)]
#[TestDox('后台角色控制器测试')]
class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        SpatiePermission::findOrCreate('roles.index', 'admin');
        SpatiePermission::findOrCreate('roles.create', 'admin');
        SpatiePermission::findOrCreate('roles.edit', 'admin');
        SpatiePermission::findOrCreate('roles.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'roles.index', 'roles.create', 'roles.edit', 'roles.delete',
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

        $email = $attributes['email'] ?? "role_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "role_adm{$suffix}",
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
     * 创建一条角色记录。
     */
    protected function makeRole(array $attributes = []): Role
    {
        return Role::create(array_merge([
            'name' => 'test_role_'.random_int(1000, 9999),
            'guard_name' => 'admin',
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问角色列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/roles');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问角色列表返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/roles');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取角色列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeRole(['name' => 'role_a']);
        $this->makeRole(['name' => 'role_b']);

        $response = $this->getJson('/admin/roles');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
            'links',
            'meta',
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    #[TestDox('角色列表页面返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/roles');
        $response->assertOk();
        $response->assertViewIs('admin.role.index');
    }

    #[Test]
    #[TestDox('角色选择器返回JSON')]
    public function test_select_returns_json(): void
    {
        $this->actingAsAdmin();
        $this->makeRole(['name' => 'selectable_role']);

        $response = $this->getJson('/admin/roles/select');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['value', 'name'],
        ]);
    }

    #[Test]
    #[TestDox('创建角色页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/roles/create');
        $response->assertOk();
        $response->assertViewIs('admin.role.create');
        $response->assertViewHas('permissions');
    }

    #[Test]
    #[TestDox('创建角色成功')]
    public function test_store_creates_role(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/roles', [
            'name' => 'new_role',
            'guard_name' => 'admin',
            'permissions' => [],
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('roles', [
            'name' => 'new_role',
            'guard_name' => 'admin',
        ]);
    }

    #[Test]
    #[TestDox('创建角色时 name 和 guard_name 必填')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/roles', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'guard_name']);
    }

    #[Test]
    #[TestDox('创建角色并分配权限')]
    public function test_store_with_permissions(): void
    {
        $this->actingAsAdmin();
        $perm = SpatiePermission::findOrCreate('test.perm', 'admin');

        $response = $this->postJson('/admin/roles', [
            'name' => 'role_with_perms',
            'guard_name' => 'admin',
            'permissions' => ['test.perm'],
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
        $role = Role::where('name', 'role_with_perms')->first();
        $this->assertTrue($role->hasPermissionTo('test.perm'));
    }

    #[Test]
    #[TestDox('编辑角色页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $role = $this->makeRole();

        $response = $this->get('/admin/roles/'.$role->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.role.edit');
        $response->assertViewHas('item');
        $response->assertViewHas('permissions');
        $response->assertViewHas('rolePermissions');
    }

    #[Test]
    #[TestDox('更新角色成功')]
    public function test_update_role(): void
    {
        $this->actingAsAdmin();
        $role = $this->makeRole(['name' => 'old_name']);

        $response = $this->putJson('/admin/roles/'.$role->id, [
            'name' => 'new_name',
            'guard_name' => 'admin',
            'permissions' => [],
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $role->refresh();
        $this->assertEquals('new_name', $role->name);
    }

    #[Test]
    #[TestDox('更新角色并同步权限')]
    public function test_update_syncs_permissions(): void
    {
        $this->actingAsAdmin();
        $perm1 = SpatiePermission::findOrCreate('sync.perm1', 'admin');
        $perm2 = SpatiePermission::findOrCreate('sync.perm2', 'admin');
        $role = $this->makeRole();
        $role->givePermissionTo($perm1);

        $response = $this->putJson('/admin/roles/'.$role->id, [
            'name' => $role->name,
            'guard_name' => 'admin',
            'permissions' => ['sync.perm2'],
        ]);

        $response->assertOk();
        $role->refresh();
        $this->assertFalse($role->hasPermissionTo('sync.perm1'));
        $this->assertTrue($role->hasPermissionTo('sync.perm2'));
    }

    #[Test]
    #[TestDox('删除普通角色成功')]
    public function test_destroy_deletes_role(): void
    {
        $this->actingAsAdmin();
        $role = $this->makeRole();

        $response = $this->deleteJson('/admin/roles/'.$role->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    #[Test]
    #[TestDox('不能删除 Super Admin 角色')]
    public function test_destroy_super_admin_role_forbidden(): void
    {
        $this->actingAsAdmin();
        $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);

        $response = $this->deleteJson('/admin/roles/'.$superAdmin->id);

        $response->assertOk();
        $response->assertJson(['code' => 1, 'message' => trans('system.default_role_cannot_delete')]);
        $this->assertDatabaseHas('roles', ['id' => $superAdmin->id]);
    }
}
