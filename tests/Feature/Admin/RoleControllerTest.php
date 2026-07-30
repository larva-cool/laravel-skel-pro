<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\RoleController;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use Database\Seeders\AdminMenuSeeder;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * 后台角色管理控制器功能测试
 */
#[CoversClass(RoleController::class)]
#[Group('admin')]
#[Group('role')]
class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AdminMenuSeeder::class, AdminSeeder::class]);

        // 登录获取 token
        $loginResponse = $this->postJson('/admin/auth/login', [
            'account' => 'admin',
            'password' => '123456',
        ]);
        $this->token = $loginResponse->json('data.access_token');
        $this->admin = Admin::query()->where('username', 'admin')->first();
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    #[Test]
    #[TestDox('未登录访问角色列表返回 401')]
    public function guest_cannot_list_roles(): void
    {
        $response = $this->getJson('/admin/roles');
        $response->assertUnauthorized();
    }

    #[Test]
    #[TestDox('获取角色列表返回 200 与分页数据')]
    public function admin_can_list_roles(): void
    {
        $response = $this->getJson('/admin/roles', $this->authHeaders());
        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'data' => ['data', 'current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonPath('data.total', 1);
    }

    #[Test]
    #[TestDox('按角色名称搜索角色')]
    public function admin_can_search_roles_by_name(): void
    {
        $response = $this->getJson('/admin/roles?role_name=super', $this->authHeaders());
        $response->assertOk()
            ->assertJsonPath('data.data.0.name', 'super_admin');
    }

    #[Test]
    #[TestDox('创建角色成功')]
    public function admin_can_create_role(): void
    {
        $response = $this->postJson('/admin/roles', [
            'name' => 'editor',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'code' => 200,
            ])
            ->assertJsonPath('data.role_name', 'editor');

        $this->assertDatabaseHas('roles', [
            'name' => 'editor',
            'guard_name' => AdminMenu::GUARD_NAME,
        ]);
    }

    #[Test]
    #[TestDox('创建角色时名称必填')]
    public function create_role_requires_name(): void
    {
        $response = $this->postJson('/admin/roles', [], $this->authHeaders());
        $response->assertUnprocessable();
    }

    #[Test]
    #[TestDox('创建角色时名称不能重复')]
    public function create_role_name_must_be_unique(): void
    {
        Role::create(['name' => 'editor', 'guard_name' => AdminMenu::GUARD_NAME]);

        $response = $this->postJson('/admin/roles', [
            'name' => 'editor',
        ], $this->authHeaders());
        $response->assertUnprocessable();
    }

    #[Test]
    #[TestDox('获取角色详情')]
    public function admin_can_view_role(): void
    {
        $role = Role::where('name', 'super_admin')->where('guard_name', AdminMenu::GUARD_NAME)->first();

        $response = $this->getJson("/admin/roles/{$role->id}", $this->authHeaders());
        $response->assertOk()
            ->assertJsonPath('data.role_id', $role->id)
            ->assertJsonPath('data.role_name', 'super_admin');
    }

    #[Test]
    #[TestDox('更新角色成功')]
    public function admin_can_update_role(): void
    {
        $role = Role::create(['name' => 'viewer', 'guard_name' => AdminMenu::GUARD_NAME]);

        $response = $this->putJson("/admin/roles/{$role->id}", [
            'name' => 'auditor',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.role_name', 'auditor');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'auditor',
        ]);
    }

    #[Test]
    #[TestDox('超级管理员角色不可修改名称')]
    public function super_admin_role_cannot_be_modified(): void
    {
        $role = Role::where('name', 'super_admin')->first();

        $response = $this->putJson("/admin/roles/{$role->id}", [
            'name' => 'hacker',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('code', 403);
    }

    #[Test]
    #[TestDox('删除角色成功')]
    public function admin_can_delete_role(): void
    {
        $role = Role::create(['name' => 'temp', 'guard_name' => AdminMenu::GUARD_NAME]);

        $response = $this->deleteJson("/admin/roles/{$role->id}", [], $this->authHeaders());
        $response->assertOk();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    #[Test]
    #[TestDox('超级管理员角色不可删除')]
    public function super_admin_role_cannot_be_deleted(): void
    {
        $role = Role::where('name', 'super_admin')->first();

        $response = $this->deleteJson("/admin/roles/{$role->id}", [], $this->authHeaders());
        $response->assertOk()
            ->assertJsonPath('code', 403);

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    #[Test]
    #[TestDox('被使用的角色不可删除')]
    public function role_in_use_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'ops', 'guard_name' => AdminMenu::GUARD_NAME]);
        $this->admin->assignRole($role);

        $response = $this->deleteJson("/admin/roles/{$role->id}", [], $this->authHeaders());
        $response->assertOk()
            ->assertJsonPath('code', 400);
    }

    #[Test]
    #[TestDox('获取角色权限返回 ID 列表')]
    public function can_get_role_permissions(): void
    {
        $role = Role::where('name', 'super_admin')->first();

        $response = $this->getJson("/admin/roles/{$role->id}/permissions", $this->authHeaders());
        $response->assertOk()->assertJson(['code' => 200]);
    }

    #[Test]
    #[TestDox('分配角色权限')]
    public function can_assign_permissions_to_role(): void
    {
        $role = Role::create(['name' => 'manager', 'guard_name' => AdminMenu::GUARD_NAME]);

        // 找两个权限
        $permIds = Permission::where('guard_name', AdminMenu::GUARD_NAME)
            ->limit(2)
            ->pluck('id')
            ->toArray();

        $response = $this->putJson("/admin/roles/{$role->id}/permissions", [
            'permissions' => $permIds,
        ], $this->authHeaders());

        $response->assertOk();
        $this->assertCount(count($permIds), $role->fresh()->permissions);
    }

    #[Test]
    #[TestDox('获取所有权限列表')]
    public function can_list_all_permissions(): void
    {
        $response = $this->getJson('/admin/roles/permissions', $this->authHeaders());
        $response->assertOk()
            ->assertJson(['code' => 200]);
    }
}
