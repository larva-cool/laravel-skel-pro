<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\Report\Report;
use App\Models\User;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台举报管理控制器测试
 */
#[CoversClass(ReportController::class)]
#[TestDox('后台举报管理控制器测试')]
class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Report::query()->delete();

        Permission::findOrCreate('reports.index', 'admin');
        Permission::findOrCreate('reports.edit', 'admin');
        Permission::findOrCreate('reports.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'reports.index', 'reports.edit', 'reports.delete',
        ]);

        $this->user = User::create([
            'name' => 'Report User',
            'email' => 'rp_user@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "rp_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "rp_adm{$suffix}",
                'name' => '举报管理员'.$suffix,
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

    protected function actingAsAdmin(?Admin $admin = null): self
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);

        return $this->actingAs($admin ?? $this->admin, 'admin');
    }

    protected function makeReport(array $attributes = []): Report
    {
        return Report::create(array_merge([
            'user_id' => $this->user->id,
            'reportable_type' => 'comment',
            'reportable_id' => 1,
            'reason' => ReportReason::SPAM->value,
            'content' => '举报内容',
            'status' => ReportStatus::PENDING->value,
            'ip_address' => '127.0.0.1',
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证访问举报列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/reports');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限访问返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/reports');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取举报列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeReport(['content' => 'A']);
        $this->makeReport(['content' => 'B', 'reason' => ReportReason::HARASSMENT->value]);

        $response = $this->getJson('/admin/reports');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('按状态筛选')]
    public function test_index_filter_by_status(): void
    {
        $this->actingAsAdmin();
        $this->makeReport(['status' => ReportStatus::PENDING->value]);
        $this->makeReport(['status' => ReportStatus::RESOLVED->value]);

        $response = $this->getJson('/admin/reports?status='.ReportStatus::RESOLVED->value);
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    #[TestDox('编辑页返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $report = $this->makeReport();

        $response = $this->get('/admin/reports/'.$report->id.'/edit');
        $response->assertOk();
        $response->assertViewIs('admin.report.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $report->id);
    }

    #[Test]
    #[TestDox('处理举报成功')]
    public function test_update_handles_report(): void
    {
        $this->actingAsAdmin();
        $report = $this->makeReport();

        $response = $this->putJson('/admin/reports/'.$report->id, [
            'status' => ReportStatus::RESOLVED->value,
            'remark' => '内容已删除',
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
        $report->refresh();
        $this->assertSame(ReportStatus::RESOLVED, $report->status);
        $this->assertSame('内容已删除', $report->remark);
        $this->assertSame($this->admin->id, $report->handled_by);
        $this->assertNotNull($report->handled_at);
    }

    #[Test]
    #[TestDox('状态字段必填')]
    public function test_update_requires_status(): void
    {
        $this->actingAsAdmin();
        $report = $this->makeReport();

        $response = $this->putJson('/admin/reports/'.$report->id, []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    #[Test]
    #[TestDox('删除举报成功')]
    public function test_destroy_deletes_report(): void
    {
        $this->actingAsAdmin();
        $report = $this->makeReport();

        $response = $this->deleteJson('/admin/reports/'.$report->id);
        $response->assertOk();
        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }
}
