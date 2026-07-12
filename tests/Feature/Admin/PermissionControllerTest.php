<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Permission;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * 后台权限控制器测试
 */
#[CoversClass(PermissionController::class)]
#[TestDox('后台权限控制器测试')]
class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        // PermissionController 使用 role:Super Admin 中间件，需要创建该角色并分配给管理员
        Role::findOrCreate('Super Admin', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->assignRole('Super Admin');
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "perm_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "perm_adm{$suffix}",
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
     * 创建一条权限记录。
     */
    protected function makePermission(array $attributes = []): Permission
    {
        return Permission::create(array_merge([
            'name' => 'test.permission.'.random_int(1000, 9999),
            'display_name' => '测试权限',
            'guard_name' => 'admin',
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问权限列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/permissions');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('非 Super Admin 角色用户访问权限列表返回403')]
    public function test_forbidden_without_super_admin_role(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/permissions');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取权限列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makePermission(['name' => 'perm.a', 'display_name' => '权限A']);
        $this->makePermission(['name' => 'perm.b', 'display_name' => '权限B']);

        $response = $this->getJson('/admin/permissions');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'display_name'],
            ],
            'links',
            'meta',
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    #[TestDox('权限列表页面返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/permissions');
        $response->assertOk();
        $response->assertViewIs('admin.permission.index');
    }

    #[Test]
    #[TestDox('创建权限页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/permissions/create');
        $response->assertOk();
        $response->assertViewIs('admin.permission.create');
    }

    #[Test]
    #[TestDox('创建权限成功')]
    public function test_store_creates_permission(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/permissions', [
            'name' => 'new.permission',
            'display_name' => '新权限',
            'guard_name' => 'admin',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'new.permission',
            'display_name' => '新权限',
        ]);
    }

    #[Test]
    #[TestDox('创建权限时 name/display_name/guard_name 必填')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/permissions', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'display_name', 'guard_name']);
    }

    #[Test]
    #[TestDox('编辑权限页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $permission = $this->makePermission();

        $response = $this->get('/admin/permissions/'.$permission->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.permission.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $permission->id);
    }

    #[Test]
    #[TestDox('更新权限成功')]
    public function test_update_permission(): void
    {
        $this->actingAsAdmin();
        $permission = $this->makePermission(['display_name' => '原名']);

        $response = $this->putJson('/admin/permissions/'.$permission->id, [
            'name' => $permission->name,
            'display_name' => '新名称',
            'guard_name' => 'admin',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $permission->refresh();
        $this->assertEquals('新名称', $permission->display_name);
    }

    #[Test]
    #[TestDox('删除权限成功')]
    public function test_destroy_deletes_permission(): void
    {
        $this->actingAsAdmin();
        $permission = $this->makePermission();

        $response = $this->deleteJson('/admin/permissions/'.$permission->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }
}
