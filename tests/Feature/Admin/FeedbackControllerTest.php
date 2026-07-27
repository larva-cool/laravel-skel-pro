<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Middleware\RefreshUserActiveAt;
use App\Models\Admin\Admin;
use App\Models\Feedback\Feedback;
use App\Models\User;
use App\Support\UserHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * 后台反馈管理控制器测试
 */
#[CoversClass(FeedbackController::class)]
#[TestDox('后台反馈管理控制器测试')]
class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Feedback::query()->delete();

        Permission::findOrCreate('feedbacks.index', 'admin');
        Permission::findOrCreate('feedbacks.edit', 'admin');
        Permission::findOrCreate('feedbacks.delete', 'admin');

        $this->admin = $this->makeAdmin();
        $this->admin->givePermissionTo([
            'feedbacks.index', 'feedbacks.edit', 'feedbacks.delete',
        ]);

        $this->user = User::create([
            'name' => 'Feedback User',
            'email' => 'fb_user@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * 创建管理员（绕过 booted 事件）
     */
    protected function makeAdmin(array $attributes = []): Admin
    {
        static $seq = 0;
        $seq++;
        $suffix = substr(md5((string) microtime(true).$seq.random_int(0, 9999)), 0, 8);

        $email = $attributes['email'] ?? "fb_adm{$suffix}@example.com";
        $phone = $attributes['phone'] ?? '13'.str_pad((string) ($seq.random_int(1000000, 9999999)), 9, '0', STR_PAD_LEFT);
        $user = UserHelper::createByEmail($email, 'password123');

        return Admin::withoutEvents(function () use ($user, $email, $suffix, $attributes, $phone) {
            $admin = new Admin;
            $fill = array_merge([
                'user_id' => $user->id,
                'username' => "fb_adm{$suffix}",
                'name' => '反馈管理员'.$suffix,
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

    protected function makeFeedback(array $attributes = []): Feedback
    {
        return Feedback::create(array_merge([
            'user_id' => $this->user->id,
            'type' => FeedbackType::SUGGESTION->value,
            'title' => '测试反馈',
            'content' => '测试反馈内容',
            'status' => FeedbackStatus::PENDING->value,
            'ip_address' => '127.0.0.1',
        ], $attributes));
    }

    #[Test]
    #[TestDox('未认证访问反馈列表被重定向')]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->withoutMiddleware(RefreshUserActiveAt::class);
        $response = $this->get('/admin/feedbacks');
        $response->assertRedirect('/admin/auth/login');
    }

    #[Test]
    #[TestDox('无权限访问返回403')]
    public function test_forbidden_without_permission(): void
    {
        $another = $this->makeAdmin();
        $this->actingAsAdmin($another);

        $response = $this->getJson('/admin/feedbacks');
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('获取反馈列表JSON')]
    public function test_index_returns_json_list(): void
    {
        $this->actingAsAdmin();
        $this->makeFeedback(['content' => 'A']);
        $this->makeFeedback(['content' => 'B', 'type' => FeedbackType::BUG->value]);

        $response = $this->getJson('/admin/feedbacks');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('按类型筛选')]
    public function test_index_filter_by_type(): void
    {
        $this->actingAsAdmin();
        $this->makeFeedback(['content' => 'A', 'type' => FeedbackType::SUGGESTION->value]);
        $this->makeFeedback(['content' => 'B', 'type' => FeedbackType::BUG->value]);

        $response = $this->getJson('/admin/feedbacks?type='.FeedbackType::BUG->value);
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    #[TestDox('编辑页返回视图')]
    public function test_edit_returns_view(): void
    {
        $this->actingAsAdmin();
        $feedback = $this->makeFeedback();

        $response = $this->get('/admin/feedbacks/'.$feedback->id.'/edit');
        $response->assertOk();
        $response->assertViewIs('admin.feedback.edit');
        $response->assertViewHas('item', fn ($item) => $item->id === $feedback->id);
    }

    #[Test]
    #[TestDox('回复反馈成功')]
    public function test_update_replies_feedback(): void
    {
        $this->actingAsAdmin();
        $feedback = $this->makeFeedback();

        $response = $this->putJson('/admin/feedbacks/'.$feedback->id, [
            'reply' => '感谢反馈，已修复',
            'status' => FeedbackStatus::REPLIED->value,
        ]);

        $response->assertOk();
        $response->assertJson(['code' => 0]);
        $feedback->refresh();
        $this->assertSame('感谢反馈，已修复', $feedback->reply);
        $this->assertSame(FeedbackStatus::REPLIED, $feedback->status);
        $this->assertSame($this->admin->id, $feedback->handled_by);
        $this->assertNotNull($feedback->handled_at);
    }

    #[Test]
    #[TestDox('回复内容必填')]
    public function test_update_requires_reply(): void
    {
        $this->actingAsAdmin();
        $feedback = $this->makeFeedback();

        $response = $this->putJson('/admin/feedbacks/'.$feedback->id, []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reply']);
    }

    #[Test]
    #[TestDox('删除反馈成功')]
    public function test_destroy_deletes_feedback(): void
    {
        $this->actingAsAdmin();
        $feedback = $this->makeFeedback();

        $response = $this->deleteJson('/admin/feedbacks/'.$feedback->id);
        $response->assertOk();
        $this->assertDatabaseMissing('feedbacks', ['id' => $feedback->id]);
    }
}
