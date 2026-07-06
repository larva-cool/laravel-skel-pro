<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\MenuController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台菜单控制器测试
 */
#[CoversClass(MenuController::class)]
#[TestDox('后台菜单控制器测试')]
class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        Permission::findOrCreate('menus.index', 'admin');
        Permission::findOrCreate('menus.create', 'admin');
        Permission::findOrCreate('menus.edit', 'admin');
        Permission::findOrCreate('menus.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'menus.index', 'menus.create', 'menus.edit', 'menus.delete',
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

        $email = $attributes['email'] ?? "menu_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "menu_adm{$suffix}",
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
     * 创建一条菜单记录。
     */
    protected function makeMenu(array $attributes = []): AdminMenu
    {
        return AdminMenu::create(array_merge([
            'title' => '测试菜单',
            'href' => '/admin/test',
            'type' => 1,
            'order' => 0,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问菜单列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/menus');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问菜单列表返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/menus');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取菜单列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeMenu(['title' => '菜单A']);
        $this->makeMenu(['title' => '菜单B']);

        $response = $this->getJson('/admin/menus');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'href', 'type', 'order'],
            ],
            'links',
            'meta',
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    #[TestDox('按 parent_id 获取子菜单列表')]
    public function test_index_filter_by_parent_id(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeMenu(['title' => '父菜单']);
        $child = $this->makeMenu(['title' => '子菜单', 'parent_id' => $parent->id]);

        $response = $this->getJson('/admin/menus?parent_id='.$parent->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($child->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('菜单列表页面返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/menus');
        $response->assertOk();
        $response->assertViewIs('admin.menu.index');
    }

    #[Test]
    #[TestDox('创建菜单页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/menus/create');
        $response->assertOk();
        $response->assertViewIs('admin.menu.create');
    }

    #[Test]
    #[TestDox('创建菜单成功')]
    public function test_store_creates_menu(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/menus', [
            'title' => '新菜单',
            'href' => '/admin/new',
            'type' => 1,
            'order' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('admin_menus', [
            'title' => '新菜单',
            'href' => '/admin/new',
        ]);
    }

    #[Test]
    #[TestDox('创建菜单时 title 和 type 必填')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/menus', [
            'href' => '/admin/test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'type', 'order']);
    }

    #[Test]
    #[TestDox('编辑菜单页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $menu = $this->makeMenu();

        $response = $this->get('/admin/menus/'.$menu->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.menu.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $menu->id);
    }

    #[Test]
    #[TestDox('更新菜单成功')]
    public function test_update_menu(): void
    {
        $this->actingAsAdmin();
        $menu = $this->makeMenu(['title' => '原标题']);

        $response = $this->putJson('/admin/menus/'.$menu->id, [
            'title' => '新标题',
            'href' => '/admin/updated',
            'type' => 1,
            'order' => 2,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $menu->refresh();
        $this->assertEquals('新标题', $menu->title);
    }

    #[Test]
    #[TestDox('删除菜单成功')]
    public function test_destroy_deletes_menu(): void
    {
        $this->actingAsAdmin();
        $menu = $this->makeMenu();

        $response = $this->deleteJson('/admin/menus/'.$menu->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertDatabaseMissing('admin_menus', ['id' => $menu->id]);
    }

    #[Test]
    #[TestDox('菜单选择器返回树形结构')]
    public function test_menu_select_returns_tree(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeMenu(['title' => '父菜单']);
        $this->makeMenu(['title' => '子菜单', 'parent_id' => $parent->id]);

        $response = $this->getJson('/admin/menus/select');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['value', 'name'],
        ]);
    }

    #[Test]
    #[TestDox('获取左侧菜单列表')]
    public function test_left_menus_returns_json(): void
    {
        $this->actingAsAdmin();
        $this->makeMenu(['title' => '菜单项', 'href' => '/admin/test']);

        $response = $this->getJson('/admin/menus/left-menus');

        $response->assertOk();
    }
}
