<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\PageController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Page;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台单页管理控制器测试
 */
#[CoversClass(PageController::class)]
#[TestDox('后台单页管理控制器测试')]
class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 清空 pages 表，避免影响测试
        Page::query()->delete();

        Permission::findOrCreate('pages.index', 'admin');
        Permission::findOrCreate('pages.create', 'admin');
        Permission::findOrCreate('pages.edit', 'admin');
        Permission::findOrCreate('pages.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'pages.index', 'pages.create', 'pages.edit', 'pages.delete',
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

        $email = $attributes['email'] ?? "page_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "page_adm{$suffix}",
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
     * 创建一条页面记录。
     */
    protected function makePage(array $attributes = []): Page
    {
        return Page::create(array_merge([
            'title' => '测试页面',
            'desc' => '测试描述',
            'content' => '测试内容',
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问页面列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/pages');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/pages');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取页面列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makePage(['title' => '页面A']);
        $this->makePage(['title' => '页面B']);

        $response = $this->getJson('/admin/pages');

        $response->assertOk();
        $response->assertJsonStructure();
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('创建页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/pages/create');
        $response->assertOk();
        $response->assertViewIs('admin.page.create');
    }

    #[Test]
    #[TestDox('编辑页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $page = $this->makePage();

        $response = $this->get('/admin/pages/'.$page->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.page.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $page->id);
        $response->assertViewHas('update_url');
    }

    #[Test]
    #[TestDox('创建页面成功')]
    public function test_store_creates_page(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/pages', [
            'title' => '新页面',
            'desc' => '新页面描述',
            'content' => '新页面内容',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('pages', [
            'title' => '新页面',
            'content' => '新页面内容',
        ]);
    }

    #[Test]
    #[TestDox('创建页面时 title/content 必填')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/pages', [
            'desc' => 'only.description',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'content']);
    }

    #[Test]
    #[TestDox('更新页面成功')]
    public function test_update_page(): void
    {
        $this->actingAsAdmin();
        $page = $this->makePage(['title' => '原标题']);

        $response = $this->putJson('/admin/pages/'.$page->id, [
            'title' => '新标题',
            'desc' => '新描述',
            'content' => '新内容',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $page->refresh();
        $this->assertEquals('新标题', $page->title);
        $this->assertEquals('新内容', $page->content);
    }

    #[Test]
    #[TestDox('删除页面成功')]
    public function test_destroy_deletes_page(): void
    {
        $this->actingAsAdmin();
        $page = $this->makePage();

        $response = $this->deleteJson('/admin/pages/'.$page->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }
}
