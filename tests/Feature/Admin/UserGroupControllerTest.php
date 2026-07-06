<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Setting;
use App\Models\User;
use App\Models\User\UserGroup;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台用户组控制器测试
 */
#[CoversClass(UserGroupController::class)]
#[TestDox('后台用户组控制器测试')]
class UserGroupControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        Permission::findOrCreate('user_groups.index', 'admin');
        Permission::findOrCreate('user_groups.create', 'admin');
        Permission::findOrCreate('user_groups.edit', 'admin');
        Permission::findOrCreate('user_groups.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'user_groups.index', 'user_groups.create', 'user_groups.edit', 'user_groups.delete',
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

        $email = $attributes['email'] ?? "ug_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "ug_adm{$suffix}",
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
     * 创建一条用户组记录。
     */
    protected function makeUserGroup(array $attributes = []): UserGroup
    {
        return UserGroup::create(array_merge([
            'name' => '测试用户组_'.random_int(1000, 9999),
            'desc' => '测试描述',
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问用户组列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/user_groups');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问用户组列表返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/user_groups');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取用户组列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeUserGroup(['name' => '用户组A']);
        $this->makeUserGroup(['name' => '用户组B']);

        $response = $this->getJson('/admin/user_groups');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'desc'],
            ],
            'links',
            'meta',
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    #[TestDox('用户组列表页面返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/user_groups');
        $response->assertOk();
        $response->assertViewIs('admin.user_group.index');
    }

    #[Test]
    #[TestDox('用户组选择器返回JSON')]
    public function test_select_returns_json(): void
    {
        $this->actingAsAdmin();
        $this->makeUserGroup(['name' => '可选用户组']);

        $response = $this->getJson('/admin/user_groups/select');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['value', 'name'],
        ]);
    }

    #[Test]
    #[TestDox('创建用户组页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/user_groups/create');
        $response->assertOk();
        $response->assertViewIs('admin.user_group.create');
    }

    #[Test]
    #[TestDox('创建用户组成功')]
    public function test_store_creates_user_group(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/user_groups', [
            'name' => '新用户组',
            'desc' => '新用户组描述',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('user_groups', [
            'name' => '新用户组',
            'desc' => '新用户组描述',
        ]);
    }

    #[Test]
    #[TestDox('创建用户组时 name 必填且唯一')]
    public function test_store_requires_unique_name(): void
    {
        $this->actingAsAdmin();
        $existing = $this->makeUserGroup(['name' => '已存在用户组']);

        $response = $this->postJson('/admin/user_groups', [
            'name' => '已存在用户组',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    #[Test]
    #[TestDox('编辑用户组页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $group = $this->makeUserGroup();

        $response = $this->get('/admin/user_groups/'.$group->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.user_group.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $group->id);
    }

    #[Test]
    #[TestDox('更新用户组成功')]
    public function test_update_user_group(): void
    {
        $this->actingAsAdmin();
        $group = $this->makeUserGroup(['name' => '原名', 'desc' => '原描述']);

        $response = $this->putJson('/admin/user_groups/'.$group->id, [
            'name' => '新名',
            'desc' => '新描述',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $group->refresh();
        $this->assertEquals('新名', $group->name);
        $this->assertEquals('新描述', $group->desc);
    }

    #[Test]
    #[TestDox('删除无用户的用户组成功')]
    public function test_destroy_deletes_empty_group(): void
    {
        $this->actingAsAdmin();
        $group = $this->makeUserGroup();

        $response = $this->deleteJson('/admin/user_groups/'.$group->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertDatabaseMissing('user_groups', ['id' => $group->id]);
    }

    #[Test]
    #[TestDox('删除有用户的用户组失败')]
    public function test_destroy_fails_with_users(): void
    {
        $this->actingAsAdmin();
        $group = $this->makeUserGroup();
        // 创建一个关联到此用户组的用户
        UserHelper::createByEmail('ug_user_'.random_int(1000, 9999).'@example.com')
            ->update(['group_id' => $group->id]);

        $response = $this->deleteJson('/admin/user_groups/'.$group->id);

        $response->assertOk();
        $response->assertJson(['code' => 1, 'message' => trans('system.user_group_has_users')]);
        $this->assertDatabaseHas('user_groups', ['id' => $group->id]);
    }
}
