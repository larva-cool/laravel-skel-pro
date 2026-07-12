<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\MailCodeController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\System\MailCode;
use App\Models\System\Setting;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台邮件验证码控制器测试
 */
#[CoversClass(MailCodeController::class)]
#[TestDox('后台邮件验证码控制器测试')]
class MailCodeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->delete();

        Permission::findOrCreate('mail_codes.index', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo(['mail_codes.index']);
    }

    /**
     * 创建管理员（绕过 booted 事件）。
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "mail_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $phone, $attributes) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "mail_adm{$suffix}",
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
     * 创建一条邮件验证码记录。
     */
    protected function makeMailCode(array $attributes = []): MailCode
    {
        return MailCode::create(array_merge([
            'email' => 'user@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
            'state' => 0,
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证用户访问邮件验证码列表被重定向到登录页')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/mail_codes');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限用户访问邮件验证码列表返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/mail_codes');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取邮件验证码列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeMailCode();
        $this->makeMailCode(['email' => 'other@example.com', 'code' => '234567']);

        $response = $this->getJson('/admin/mail_codes');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'email', 'code', 'state', 'state_label', 'verify_count', 'send_at'],
            ],
            'links',
            'meta',
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    #[TestDox('邮件验证码列表页面返回视图')]
    public function test_index_returns_view(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/mail_codes');

        $response->assertOk();
        $response->assertViewIs('admin.mail_code.index');
    }

    #[Test]
    #[TestDox('邮件验证码列表按ID倒序排列')]
    public function test_index_orders_by_id_desc(): void
    {
        $this->actingAsAdmin();
        $first = $this->makeMailCode();
        $second = $this->makeMailCode(['email' => 'other@example.com']);

        $response = $this->getJson('/admin/mail_codes');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals($second->id, $data[0]['id']);
    }

    #[Test]
    #[TestDox('按邮箱筛选邮件验证码列表')]
    public function test_index_filters_by_email(): void
    {
        $this->actingAsAdmin();
        $this->makeMailCode(['email' => 'alice@example.com']);
        $this->makeMailCode(['email' => 'bob@sample.com']);

        $response = $this->getJson('/admin/mail_codes?email=alice');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('alice@example.com', $data[0]['email']);
    }

    #[Test]
    #[TestDox('按状态筛选邮件验证码列表')]
    public function test_index_filters_by_state(): void
    {
        $this->actingAsAdmin();
        $this->makeMailCode(['state' => 0]);
        $this->makeMailCode(['email' => 'other@example.com', 'state' => 1]);

        $response = $this->getJson('/admin/mail_codes?state=1');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(1, $data[0]['state']);
        $this->assertEquals('已使用', $data[0]['state_label']);
    }
}
