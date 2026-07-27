<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Models\Feedback\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 反馈控制器测试
 */
#[CoversClass(FeedbackController::class)]
#[TestDox('用户反馈控制器测试')]
class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Feedback User',
            'email' => 'feedback_user@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->otherUser = User::create([
            'name' => 'Other User',
            'email' => 'feedback_other@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createFeedback(array $attributes = []): Feedback
    {
        return Feedback::create(array_merge([
            'user_id' => $this->user->id,
            'type' => FeedbackType::SUGGESTION->value,
            'title' => '默认标题',
            'content' => '默认反馈内容',
            'status' => FeedbackStatus::PENDING->value,
            'ip_address' => '127.0.0.1',
        ], $attributes));
    }

    #[Test]
    #[TestDox('提交反馈需要登录')]
    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/feedbacks', [
            'type' => FeedbackType::SUGGESTION->value,
            'content' => '测试反馈',
        ]);
        $response->assertStatus(401);
    }

    #[Test]
    #[TestDox('提交反馈成功')]
    public function test_store_success(): void
    {
        $payload = [
            'type' => FeedbackType::BUG->value,
            'title' => '页面报错',
            'content' => '页面打开就白屏了',
            'contact' => 'me@example.com',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/feedbacks', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id', 'type', 'title', 'content', 'status', 'created_at',
        ]);
        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $this->user->id,
            'type' => FeedbackType::BUG->value,
            'title' => '页面报错',
            'content' => '页面打开就白屏了',
            'status' => FeedbackStatus::PENDING->value,
        ]);
    }

    #[Test]
    #[TestDox('提交反馈参数验证')]
    public function test_store_validation(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/feedbacks', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type', 'content']);

        $response = $this->actingAs($this->user)->postJson('/api/v1/feedbacks', [
            'type' => 'invalid',
            'content' => '',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type', 'content']);

        $response = $this->actingAs($this->user)->postJson('/api/v1/feedbacks', [
            'type' => FeedbackType::OTHER->value,
            'content' => str_repeat('a', 2001),
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    #[Test]
    #[TestDox('我的反馈列表仅返回本人')]
    public function test_index_returns_only_current_user(): void
    {
        $this->createFeedback(['content' => 'A']);
        $this->createFeedback(['content' => 'B']);
        $this->createFeedback([
            'user_id' => $this->otherUser->id,
            'content' => 'other',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/feedbacks');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('详情接口无法访问他人反馈')]
    public function test_show_forbidden_for_others(): void
    {
        $feedback = $this->createFeedback([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/feedbacks/'.$feedback->id);
        $response->assertStatus(403);
    }

    #[Test]
    #[TestDox('详情接口可访问本人反馈')]
    public function test_show_success(): void
    {
        $feedback = $this->createFeedback(['content' => '我的反馈']);
        $response = $this->actingAs($this->user)->getJson('/api/v1/feedbacks/'.$feedback->id);
        $response->assertOk();
        $response->assertJson([
            'id' => $feedback->id,
            'content' => '我的反馈',
        ]);
    }
}
