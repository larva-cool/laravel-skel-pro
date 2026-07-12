<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\AreaController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Area;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台地区管理控制器测试
 */
#[CoversClass(AreaController::class)]
#[TestDox('后台地区管理控制器测试')]
class AreaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 清空 areas 表，避免影响测试
        Area::query()->forceDelete();

        Permission::findOrCreate('areas.index', 'admin');
        Permission::findOrCreate('areas.create', 'admin');
        Permission::findOrCreate('areas.edit', 'admin');
        Permission::findOrCreate('areas.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'areas.index', 'areas.create', 'areas.edit', 'areas.delete',
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

        $email = $attributes['email'] ?? "area_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "area_adm{$suffix}",
                'name' => '测试管理员'.$suffix,
                'email' => $email,
                'phone' => $phone,
                'status' => 1,
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
     * 以管理员身份登录并禁用 RefreshUserActiveAt 中间件。
     */
    protected function actingAsAdmin(?Admin $admin = null): self
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);

        return $this->actingAs($admin ?? $this->admin, 'admin');
    }

    /**
     * 创建一条地区记录。
     */
    protected function makeArea(array $attributes = []): Area
    {
        return Area::create(array_merge([
            'name' => '测试地区',
            'area_code' => rand(100000, 999999),
            'city_code' => '010',
            'order' => 0,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问地区列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/areas');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/areas');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取地区列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeArea(['name' => '地区A']);
        $this->makeArea(['name' => '地区B']);

        $response = $this->getJson('/admin/areas');

        $response->assertOk();
        $response->assertJsonStructure();
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('按父ID筛选地区')]
    public function test_index_filter_by_parent_id(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeArea(['name' => 'Parent']);
        $child = $this->makeArea(['name' => 'Child', 'parent_id' => $parent->id]);
        $other = $this->makeArea(['name' => 'Other']);

        $response = $this->getJson('/admin/areas?parent_id='.$parent->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($child->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('获取 Xm-select 菜单数据')]
    public function test_select_returns_tree_data(): void
    {
        $this->actingAsAdmin();
        $this->makeArea(['name' => '北京']);
        $this->makeArea(['name' => '上海']);

        $response = $this->getJson('/admin/areas/select');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['value', 'name', 'icon'],
        ]);
    }

    #[Test]
    #[TestDox('获取子地区数据')]
    public function test_children_returns_children(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeArea(['name' => 'Parent']);
        $child = $this->makeArea(['name' => 'Child', 'parent_id' => $parent->id]);

        $response = $this->getJson('/admin/areas/children?id='.$parent->id);

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals($child->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('创建页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/areas/create');
        $response->assertOk();
        $response->assertViewIs('admin.area.create');
    }

    #[Test]
    #[TestDox('编辑页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $area = $this->makeArea();

        $response = $this->get('/admin/areas/'.$area->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.area.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $area->id);
    }

    #[Test]
    #[TestDox('创建地区成功')]
    public function test_store_creates_area(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/areas', [
            'name' => '新地区',
            'area_code' => 123456,
            'city_code' => '020',
            'order' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('areas', [
            'name' => '新地区',
            'area_code' => 123456,
        ]);
    }

    #[Test]
    #[TestDox('创建地区时 name 必填')]
    public function test_store_requires_name(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/areas', [
            'area_code' => 123456,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    #[Test]
    #[TestDox('更新地区成功')]
    public function test_update_area(): void
    {
        $this->actingAsAdmin();
        $area = $this->makeArea(['name' => '原名']);

        $response = $this->putJson('/admin/areas/'.$area->id, [
            'name' => '新名称',
            'area_code' => 654321,
            'city_code' => '021',
            'order' => 2,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $area->refresh();
        $this->assertEquals('新名称', $area->name);
        $this->assertEquals(654321, $area->area_code);
    }

    #[Test]
    #[TestDox('删除地区成功')]
    public function test_destroy_deletes_area(): void
    {
        $this->actingAsAdmin();
        $area = $this->makeArea();

        $response = $this->deleteJson('/admin/areas/'.$area->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertSoftDeleted('areas', ['id' => $area->id]);
    }
}
