<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enum\StatusSwitch;
use App\Enum\UserType;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\Announcement\Announcement;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台公告控制器测试
 */
#[CoversClass(AnnouncementController::class)]
#[TestDox('后台公告控制器测试')]
class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('announcements.index', 'admin');
        Permission::findOrCreate('announcements.create', 'admin');
        Permission::findOrCreate('announcements.edit', 'admin');
        Permission::findOrCreate('announcements.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'announcements.index', 'announcements.create', 'announcements.edit', 'announcements.delete',
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

        $email = $attributes['email'] ?? "ann_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "ann_adm{$suffix}",
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
     * 创建一条公告。
     */
    protected function makeAnnouncement(array $attributes = []): Announcement
    {
        return Announcement::create(array_merge([
            'coverage' => [UserType::USER->value],
            'title' => '测试公告'.Str::random(4),
            'content' => '这是公告内容',
            'status' => StatusSwitch::ENABLED->value,
            'effective_time_type' => 0,
            'admin_id' => $this->admin->id,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问公告列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/announcements');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/announcements');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取公告列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeAnnouncement(['title' => '公告A']);
        $this->makeAnnouncement(['title' => '公告B']);

        $response = $this->getJson('/admin/announcements');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'coverage', 'status', 'read_count'],
            ],
            'links',
            'meta',
        ]);
        $this->assertCount(2, $response->json('data'));
        // 列表应按 id 倒序
        $data = $response->json('data');
        $this->assertGreaterThan($data[1]['id'], $data[0]['id']);
    }

    #[Test]
    #[TestDox('创建页面返回视图并包含必要数据')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/announcements/create');
        $response->assertOk();
        $response->assertViewIs('admin.announcement.create');
        $response->assertViewHas('coverage_options');
        $response->assertViewHas('effective_time_type_options');
    }

    #[Test]
    #[TestDox('编辑页面返回视图并包含必要数据')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        // 视图中 Datetimerange 组件要求 start/end 值不能为 null，创建时使用定时生效并给时间字段
        $announcement = $this->makeAnnouncement([
            'effective_time_type' => 1,
            'effective_start_time' => now()->subDay(),
            'effective_end_time' => now()->addDays(7),
        ]);

        $response = $this->get('/admin/announcements/'.$announcement->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.announcement.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $announcement->id);
        $response->assertViewHas('coverage_options');
        $response->assertViewHas('effective_time_type_options');
    }

    #[Test]
    #[TestDox('创建公告成功')]
    public function test_store_creates_announcement(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/announcements', [
            'coverage' => UserType::USER->value,
            'title' => '系统升级公告',
            'content' => '平台将于今晚进行维护...',
            'status' => StatusSwitch::ENABLED->value,
            'effective_time_type' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('announcements', [
            'title' => '系统升级公告',
            'admin_id' => $this->admin->id,
        ]);
    }

    #[Test]
    #[TestDox('创建公告必填字段验证（coverage/title/content/effective_time_type）')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        // status 在 prepareForValidation 中通过 $this->integer('status') 在未传时默认为 0（即 StatusSwitch::DISABLED），
        // 因此实际必填的是 coverage/title/content/effective_time_type
        $response = $this->postJson('/admin/announcements', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'coverage', 'title', 'content', 'effective_time_type',
        ]);
    }

    #[Test]
    #[TestDox('定时发布公告缺少时间字段验证')]
    public function test_store_timed_requires_time_range(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/announcements', [
            'coverage' => UserType::USER->value,
            'title' => '定时公告',
            'content' => '内容',
            'status' => StatusSwitch::ENABLED->value,
            'effective_time_type' => 1, // 定时有效
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['effective_start_time', 'effective_end_time']);
    }

    #[Test]
    #[TestDox('更新公告成功')]
    public function test_update_announcement(): void
    {
        $this->actingAsAdmin();
        $announcement = $this->makeAnnouncement(['title' => '原标题']);

        $response = $this->putJson('/admin/announcements/'.$announcement->id, [
            'title' => '新标题',
            'content' => '新内容',
            'status' => StatusSwitch::ENABLED->value,
            'effective_time_type' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $announcement->refresh();
        $this->assertEquals('新标题', $announcement->title);
        $this->assertEquals('新内容', $announcement->content);
    }

    #[Test]
    #[TestDox('更新公告状态成功')]
    public function test_update_status(): void
    {
        $this->actingAsAdmin();
        $announcement = $this->makeAnnouncement(['status' => StatusSwitch::ENABLED->value]);

        $response = $this->postJson('/admin/announcements/status', [
            'id' => $announcement->id,
            'status' => StatusSwitch::DISABLED->value,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $announcement->refresh();
        $this->assertEquals(StatusSwitch::DISABLED->value, $announcement->status->value);
    }

    #[Test]
    #[TestDox('更新状态缺少 id 验证失败')]
    public function test_update_status_validation(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/announcements/status', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id', 'status']);
    }

    #[Test]
    #[TestDox('更新状态时传入不存在的 id 返回404')]
    public function test_update_status_not_found(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/announcements/status', [
            'id' => 999999,
            'status' => StatusSwitch::DISABLED->value,
        ]);
        $response->assertStatus(404);
    }

    #[Test]
    #[TestDox('删除公告成功')]
    public function test_destroy_deletes_announcement(): void
    {
        $this->actingAsAdmin();
        $announcement = $this->makeAnnouncement();

        $response = $this->deleteJson('/admin/announcements/'.$announcement->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    #[Test]
    #[TestDox('创建公告时自动设置 admin_id 为当前登录管理员')]
    public function test_store_auto_sets_admin_id(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/admin/announcements', [
            'coverage' => UserType::USER->value,
            'title' => '自动填充管理员ID',
            'content' => '内容...',
            'status' => StatusSwitch::ENABLED->value,
            'effective_time_type' => 0,
        ]);

        $item = Announcement::query()->where('title', '自动填充管理员ID')->first();
        $this->assertNotNull($item);
        $this->assertEquals($this->admin->id, $item->admin_id);
    }
}
