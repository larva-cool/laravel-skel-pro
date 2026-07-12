<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\AgreementController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\Agreement;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台用户协议控制器测试
 */
#[CoversClass(AgreementController::class)]
#[TestDox('后台用户协议控制器测试')]
class AgreementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('agreements.index', 'admin');
        Permission::findOrCreate('agreements.create', 'admin');
        Permission::findOrCreate('agreements.edit', 'admin');
        Permission::findOrCreate('agreements.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'agreements.index', 'agreements.create', 'agreements.edit', 'agreements.delete',
        ]);
    }

    /**
     * 创建管理员，绕过 booted 事件中对 phone 的处理。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "agre_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "adm{$suffix}",
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
     * 以指定管理员身份登录，并禁用 RefreshUserActiveAt 中间件（Admin 模型未实现 refreshLastActiveAt 方法）。
     */
    protected function actingAsAdmin(?Admin $admin = null): self
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);

        return $this->actingAs($admin ?? $this->admin, 'admin');
    }

    /**
     * 创建一条协议记录。
     */
    protected function makeAgreement(array $attributes = []): Agreement
    {
        return Agreement::create(array_merge([
            'type' => 'user_agreement',
            'title' => '用户协议'.Str::random(4),
            'content' => '这是协议内容',
            'status' => 1,
            'order' => 0,
            'admin_id' => $this->admin->id,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问协议列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/agreements');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/agreements');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取协议列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeAgreement(['title' => '协议A', 'type' => 'user']);
        $this->makeAgreement(['title' => '协议B', 'type' => 'privacy']);

        $response = $this->getJson('/admin/agreements');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'type', 'status', 'order'],
            ],
            'links',
            'meta',
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('创建页面返回视图')]
    public function test_create_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/agreements/create');
        $response->assertOk();
        $response->assertViewIs('admin.agreement.create');
    }

    #[Test]
    #[TestDox('编辑页面返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $agreement = $this->makeAgreement();

        $response = $this->get('/admin/agreements/'.$agreement->id.'/edit');

        $response->assertOk();
        $response->assertViewIs('admin.agreement.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $agreement->id);
    }

    #[Test]
    #[TestDox('创建协议成功')]
    public function test_store_creates_agreement(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/admin/agreements', [
            'type' => 'user_agreement',
            'title' => '用户服务协议',
            'content' => '欢迎使用...',
            'status' => true,
            'order' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.create_success')]);
        $this->assertDatabaseHas('agreements', [
            'type' => 'user_agreement',
            'title' => '用户服务协议',
            'admin_id' => $this->admin->id,
        ]);
    }

    #[Test]
    #[TestDox('创建协议时 type/title/content 必填')]
    public function test_store_requires_fields(): void
    {
        $this->actingAsAdmin();

        // status 在 prepareForValidation 中会通过 $this->boolean('status') 默认为 false，
        // 因此只验证 type/title/content 必填
        $response = $this->postJson('/admin/agreements', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type', 'title', 'content']);
    }

    #[Test]
    #[TestDox('更新协议成功')]
    public function test_update_agreement(): void
    {
        $this->actingAsAdmin();
        $agreement = $this->makeAgreement(['title' => '原标题']);

        $response = $this->putJson('/admin/agreements/'.$agreement->id, [
            'title' => '新标题',
            'content' => '新内容',
            'status' => true,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.update_success')]);

        $agreement->refresh();
        $this->assertEquals('新标题', $agreement->title);
        $this->assertEquals('新内容', $agreement->content);
    }

    #[Test]
    #[TestDox('删除协议成功')]
    public function test_destroy_deletes_agreement(): void
    {
        $this->actingAsAdmin();
        $agreement = $this->makeAgreement();

        $response = $this->deleteJson('/admin/agreements/'.$agreement->id);

        $response->assertOk();
        $response->assertJson(['code' => 0, 'message' => trans('system.delete_success')]);

        // Agreement 模型没有 SoftDeletes，应是物理删除
        $this->assertDatabaseMissing('agreements', ['id' => $agreement->id]);
    }

    #[Test]
    #[TestDox('创建协议时自动设置 admin_id 为当前登录管理员')]
    public function test_store_auto_sets_admin_id(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/admin/agreements', [
            'type' => 'privacy',
            'title' => '隐私协议',
            'content' => '隐私声明内容...',
            'status' => true,
        ]);

        $agreement = Agreement::query()->where('type', 'privacy')->first();
        $this->assertNotNull($agreement);
        $this->assertEquals($this->admin->id, $agreement->admin_id);
    }
}
