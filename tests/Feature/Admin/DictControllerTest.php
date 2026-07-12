<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\DictController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Dict;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台字典管理控制器测试
 */
#[CoversClass(DictController::class)]
#[TestDox('后台字典管理控制器测试')]
class DictControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 清空 dicts 表，避免影响测试
        Dict::query()->forceDelete();

        Permission::findOrCreate('admins.index', 'admin');
        Permission::findOrCreate('admins.create', 'admin');
        Permission::findOrCreate('admins.edit', 'admin');
        Permission::findOrCreate('admins.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'admins.index', 'admins.create', 'admins.edit', 'admins.delete',
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

        $email = $attributes['email'] ?? "dict_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "dict_adm{$suffix}",
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
     * 创建一条字典记录。
     */
    protected function makeDict(array $attributes = []): Dict
    {
        return Dict::create(array_merge([
            'name' => '测试字典',
            'code' => 'test_dict_'.rand(1000, 9999),
            'description' => '测试描述',
            'status' => 1,
            'order' => 0,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问字典列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/dicts');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/dicts');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取字典列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeDict(['name' => '字典A', 'code' => 'dict_a']);
        $this->makeDict(['name' => '字典B', 'code' => 'dict_b']);

        $response = $this->getJson('/admin/dicts');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'code', 'description', 'status', 'order'],
            ],
            'links',
            'meta',
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('按名称搜索字典')]
    public function test_index_search_by_name(): void
    {
        $this->actingAsAdmin();
        $target = $this->makeDict(['name' => 'SpecialDict', 'code' => 'special']);
        $this->makeDict(['name' => 'OtherDict']);

        $response = $this->getJson('/admin/dicts?name=SpecialDict');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($target->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('按父ID筛选字典')]
    public function test_index_filter_by_parent_id(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeDict(['name' => 'Parent', 'code' => 'parent']);
        $child = $this->makeDict(['name' => 'Child', 'code' => 'child', 'parent_id' => $parent->id]);
        $other = $this->makeDict(['name' => 'Other', 'code' => 'other']);

        $response = $this->getJson('/admin/dicts?parent_id='.$parent->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($child->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('创建页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/dicts/create');
        $response->assertOk();
        $response->assertViewIs('admin.dict.create');
    }

    #[Test]
    #[TestDox('创建字典数据页面返回视图')]
    public function test_create_data_returns_view(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeDict();
        $response = $this->get('/admin/dicts/create_data?parent_id='.$parent->id);
        $response->assertOk();
        $response->assertViewIs('admin.dict.create_data');
        $response->assertViewHas('parent_id', $parent->id);
    }

    #[Test]
    #[TestDox('编辑页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $dict = $this->makeDict();

        $response = $this->get('/admin/dicts/'.$dict->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.dict.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $dict->id);
    }

    #[Test]
    #[TestDox('编辑字典数据页面返回视图')]
    public function test_edit_data_data_returns_view(): void
    {
        $this->actingAsAdmin();
        $dict = $this->makeDict();

        $response = $this->get('/admin/dicts/edit_data/'.$dict->id);

        $response->assertOk();
        $response->assertViewIs('admin.dict.edit_data');
        $response->assertViewHas('item', fn ($item) => $item->id === $dict->id);
    }

    #[Test]
    #[TestDox('创建字典成功')]
    public function test_store_creates_dict(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/dicts', [
            'name' => '新字典',
            'code' => 'new_dict',
            'description' => '新字典描述',
            'status' => 1,
            'order' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('dicts', [
            'name' => '新字典',
            'code' => 'new_dict',
        ]);
    }

    #[Test]
    #[TestDox('创建字典时 name/code 必填')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/dicts', [
            'description' => 'only.description',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'code']);
    }

    #[Test]
    #[TestDox('创建字典数据成功')]
    public function test_store_data_creates_dict(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeDict();

        $response = $this->postJson('/admin/dicts/store_data', [
            'parent_id' => $parent->id,
            'name' => '子字典',
            'code' => 'child_dict',
            'description' => '子字典描述',
            'status' => 1,
            'order' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('dicts', [
            'parent_id' => $parent->id,
            'name' => '子字典',
            'code' => 'child_dict',
        ]);
    }

    #[Test]
    #[TestDox('更新字典成功')]
    public function test_update_dict(): void
    {
        $this->actingAsAdmin();
        $dict = $this->makeDict(['name' => '原名', 'code' => 'old_code']);

        $response = $this->putJson('/admin/dicts/'.$dict->id, [
            'name' => '新名称',
            'code' => 'new_code',
            'description' => '新描述',
            'status' => 1,
            'order' => 2,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $dict->refresh();
        $this->assertEquals('新名称', $dict->name);
        $this->assertEquals('new_code', $dict->code);
    }

    #[Test]
    #[TestDox('更新字典状态成功')]
    public function test_update_status_success(): void
    {
        $this->actingAsAdmin();
        $dict = $this->makeDict(['status' => 1]);

        $response = $this->postJson('/admin/dicts/status', [
            'id' => $dict->id,
            'status' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $dict->refresh();
        $this->assertEquals(0, $dict->status->value);
    }

    #[Test]
    #[TestDox('删除字典成功')]
    public function test_destroy_deletes_dict(): void
    {
        $this->actingAsAdmin();
        $dict = $this->makeDict();

        $response = $this->deleteJson('/admin/dicts/'.$dict->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertSoftDeleted('dicts', ['id' => $dict->id]);
    }

    #[Test]
    #[TestDox('删除有子字典的字典失败')]
    public function test_destroy_fails_with_children(): void
    {
        $this->actingAsAdmin();
        $parent = $this->makeDict();
        $this->makeDict(['parent_id' => $parent->id]);

        $response = $this->deleteJson('/admin/dicts/'.$parent->id);

        $response->assertOk();
        $response->assertJson(['code' => 1, 'message' => '请先删除子字典']);
        $this->assertDatabaseHas('dicts', ['id' => $parent->id]);
    }

    #[Test]
    #[TestDox('批量删除字典成功')]
    public function test_batch_destroy_deletes_dicts(): void
    {
        $this->actingAsAdmin();
        $dict1 = $this->makeDict();
        $dict2 = $this->makeDict();

        $response = $this->postJson('/admin/dicts/batch_destroy', [
            'ids' => [$dict1->id, $dict2->id],
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertSoftDeleted('dicts', ['id' => $dict1->id]);
        $this->assertSoftDeleted('dicts', ['id' => $dict2->id]);
    }
}
