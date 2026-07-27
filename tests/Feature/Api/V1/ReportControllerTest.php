<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Http\Controllers\Api\V1\ReportController;
use App\Models\Content\Comment;
use App\Models\Report\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 举报控制器测试
 */
#[CoversClass(ReportController::class)]
#[TestDox('用户举报控制器测试')]
class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Report User',
            'email' => 'report_user@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function makeComment(): Comment
    {
        return Comment::create([
            'user_id' => $this->user->id,
            'source_id' => 1,
            'source_type' => 'comment',
            'content' => '被举报评论',
        ]);
    }

    #[Test]
    #[TestDox('提交举报需要登录')]
    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/reports', [
            'reportable_type' => 'comment',
            'reportable_id' => 1,
            'reason' => ReportReason::SPAM->value,
        ]);
        $response->assertStatus(401);
    }

    #[Test]
    #[TestDox('提交举报成功')]
    public function test_store_success(): void
    {
        $comment = $this->makeComment();

        $response = $this->actingAs($this->user)->postJson('/api/v1/reports', [
            'reportable_type' => 'comment',
            'reportable_id' => $comment->id,
            'reason' => ReportReason::SPAM->value,
            'content' => '这是广告',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id', 'reportable_type', 'reportable_id', 'reason', 'status', 'created_at',
        ]);
        $this->assertDatabaseHas('reports', [
            'user_id' => $this->user->id,
            'reportable_type' => 'comment',
            'reportable_id' => $comment->id,
            'reason' => ReportReason::SPAM->value,
            'status' => ReportStatus::PENDING->value,
        ]);
    }

    #[Test]
    #[TestDox('目标类型必须在白名单内')]
    public function test_reportable_type_whitelist(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/reports', [
            'reportable_type' => 'unknown',
            'reportable_id' => 1,
            'reason' => ReportReason::SPAM->value,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reportable_type']);
    }

    #[Test]
    #[TestDox('必填参数校验')]
    public function test_validation_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/reports', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reportable_type', 'reportable_id', 'reason']);
    }

    #[Test]
    #[TestDox('允许对用户进行举报')]
    public function test_can_report_user(): void
    {
        $target = User::create([
            'name' => 'Target',
            'email' => 'target_user@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/reports', [
            'reportable_type' => 'user',
            'reportable_id' => $target->id,
            'reason' => ReportReason::HARASSMENT->value,
            'content' => '骚扰',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reports', [
            'user_id' => $this->user->id,
            'reportable_type' => 'user',
            'reportable_id' => $target->id,
        ]);
    }

    #[Test]
    #[TestDox('允许同一用户重复举报（不去重）')]
    public function test_duplicate_reports_allowed(): void
    {
        $comment = $this->makeComment();
        $payload = [
            'reportable_type' => 'comment',
            'reportable_id' => $comment->id,
            'reason' => ReportReason::SPAM->value,
        ];

        $this->actingAs($this->user)->postJson('/api/v1/reports', $payload)->assertStatus(201);
        $this->actingAs($this->user)->postJson('/api/v1/reports', $payload)->assertStatus(201);

        $this->assertSame(2, Report::query()->count());
    }
}
