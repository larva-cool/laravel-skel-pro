<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enum\ReviewStatus;
use App\Http\Controllers\Api\V1\LikeController;
use App\Models\Content\Comment;
use App\Models\Content\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 点赞控制器测试类
 */
#[CoversClass(LikeController::class)]
#[TestDox('点赞控制器测试')]
class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /**
     * 创建一个用于被点赞的评论
     */
    protected function createComment(array $overrides = []): Comment
    {
        return Comment::create(array_merge([
            'user_id' => $this->otherUser->id,
            'source_id' => 1,
            'source_type' => 'comment',
            'content' => '待被点赞的评论',
            'status' => ReviewStatus::APPROVED,
        ], $overrides));
    }

    #[Test]
    #[TestDox('测试未认证用户无法访问点赞端点')]
    public function test_authentication_required()
    {
        $this->getJson('/api/v1/likes')->assertUnauthorized();
        $this->postJson('/api/v1/likes', [])->assertUnauthorized();
        $this->deleteJson('/api/v1/likes/1')->assertUnauthorized();
    }

    #[Test]
    #[TestDox('测试获取我的点赞列表')]
    public function test_index_returns_own_likes()
    {
        $comment1 = $this->createComment();
        $comment2 = $this->createComment();

        // 我的点赞
        Like::create([
            'user_id' => $this->user->id,
            'source_id' => $comment1->id,
            'source_type' => 'comment',
        ]);

        // 别人的点赞
        Like::create([
            'user_id' => $this->otherUser->id,
            'source_id' => $comment2->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/likes');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'source_id', 'source_type', 'created_at', 'updated_at'],
            ],
            'links',
            'meta',
        ]);
        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    #[TestDox('测试按 type 参数过滤点赞列表')]
    public function test_index_filters_by_type()
    {
        $comment = $this->createComment();

        Like::create([
            'user_id' => $this->user->id,
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/likes?type=comment');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('comment', $response->json('data.0.source_type'));
    }

    #[Test]
    #[TestDox('测试点赞成功')]
    public function test_store_creates_like()
    {
        $comment = $this->createComment();

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/likes', [
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => trans('system.like_success')]);
        $this->assertDatabaseHas('likes', [
            'user_id' => $this->user->id,
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        // 验证点赞计数增加
        $comment->refresh();
        $this->assertEquals(1, $comment->like_count);
    }

    #[Test]
    #[TestDox('测试重复点赞返回错误')]
    public function test_store_duplicate_returns_error()
    {
        $comment = $this->createComment();

        Like::create([
            'user_id' => $this->user->id,
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/likes', [
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => trans('system.like_exist')]);
    }

    #[Test]
    #[TestDox('测试点赞参数验证')]
    public function test_store_validation()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/likes', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_id', 'source_type']);
    }

    #[Test]
    #[TestDox('测试无效 source_type 校验失败')]
    public function test_store_invalid_source_type()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/likes', [
            'source_id' => 1,
            'source_type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_type']);
    }

    #[Test]
    #[TestDox('测试取消自己的点赞')]
    public function test_destroy_deletes_own_like()
    {
        $comment = $this->createComment();
        $like = Like::create([
            'user_id' => $this->user->id,
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/likes/'.$like->id);

        $response->assertOk();
        $response->assertJson(['message' => trans('system.like_cancel_success')]);
        $this->assertDatabaseMissing('likes', ['id' => $like->id]);
    }

    #[Test]
    #[TestDox('测试无法取消他人的点赞')]
    public function test_destroy_does_not_delete_others_like()
    {
        $comment = $this->createComment();
        $like = Like::create([
            'user_id' => $this->otherUser->id,
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/likes/'.$like->id);

        // 控制器只在 user_id 匹配时删除，不匹配时也返回 200
        $response->assertOk();
        $this->assertDatabaseHas('likes', ['id' => $like->id]);
    }
}
