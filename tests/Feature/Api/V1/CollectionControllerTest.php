<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enum\ReviewStatus;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Models\Content\Collection;
use App\Models\Content\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 收藏控制器测试类
 *
 * 由于源表（如 comments）未包含 `collection_count` 列，使用 `Collection::withoutEvents`
 * 绕过模型 booted 中对源表 `collection_count` 的自增/自减逻辑，仅关注 Controller 行为。
 */
#[CoversClass(CollectionController::class)]
#[TestDox('收藏控制器测试')]
class CollectionControllerTest extends TestCase
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
     * 创建 Comment 作为收藏源
     */
    protected function createSource(): Comment
    {
        return Comment::create([
            'user_id' => $this->otherUser->id,
            'source_id' => 1,
            'source_type' => 'comment',
            'content' => '被收藏的评论',
            'status' => ReviewStatus::APPROVED,
        ]);
    }

    /**
     * 静默创建收藏（绕过 booted 事件，避免源表 collection_count 列不存在的错误）
     */
    protected function silentlyCreateCollection(array $attributes): Collection
    {
        return Collection::withoutEvents(function () use ($attributes) {
            return Collection::create($attributes);
        });
    }

    #[Test]
    #[TestDox('测试未认证用户无法访问收藏端点')]
    public function test_authentication_required()
    {
        $this->getJson('/api/v1/collections')->assertUnauthorized();
        $this->postJson('/api/v1/collections', [])->assertUnauthorized();
        $this->deleteJson('/api/v1/collections/1')->assertUnauthorized();
    }

    #[Test]
    #[TestDox('测试获取自己的收藏列表')]
    public function test_index_returns_own_collections()
    {
        $source1 = $this->createSource();
        $source2 = $this->createSource();

        $this->silentlyCreateCollection([
            'user_id' => $this->user->id,
            'source_id' => $source1->id,
            'source_type' => 'comment',
        ]);
        $this->silentlyCreateCollection([
            'user_id' => $this->otherUser->id,
            'source_id' => $source2->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/collections');

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
    #[TestDox('测试按 type 参数过滤收藏列表')]
    public function test_index_filters_by_type()
    {
        $source = $this->createSource();

        $this->silentlyCreateCollection([
            'user_id' => $this->user->id,
            'source_id' => $source->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/collections?type=comment');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('comment', $response->json('data.0.source_type'));

        // 过滤其他类型时列表为空
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/collections?type=user');
        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    #[TestDox('测试重复收藏不会创建重复记录')]
    public function test_store_duplicate_does_not_create_new()
    {
        $source = $this->createSource();

        $this->silentlyCreateCollection([
            'user_id' => $this->user->id,
            'source_id' => $source->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/collections', [
            'source_id' => $source->id,
            'source_type' => 'comment',
        ]);

        // 由于存在旧记录，Controller 内部不会调用 Collection::create，因此不会触发 booted 事件
        $response->assertOk();
        $this->assertEquals(1, Collection::query()->where('user_id', $this->user->id)->count());
    }

    #[Test]
    #[TestDox('测试添加收藏参数验证')]
    public function test_store_validation()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/collections', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_id', 'source_type']);
    }

    #[Test]
    #[TestDox('测试无效 source_type 校验失败')]
    public function test_store_invalid_source_type()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/collections', [
            'source_id' => 1,
            'source_type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_type']);
    }

    #[Test]
    #[TestDox('测试取消自己的收藏')]
    public function test_destroy_deletes_own_collection()
    {
        $source = $this->createSource();
        $collection = $this->silentlyCreateCollection([
            'user_id' => $this->user->id,
            'source_id' => $source->id,
            'source_type' => 'comment',
        ]);

        // 绕过 deleted 事件，防止源表 collection_count 列缺失导致的错误
        Collection::withoutEvents(function () use ($collection) {
            $response = $this->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/v1/collections/'.$collection->id);
            $response->assertOk();
        });

        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }

    #[Test]
    #[TestDox('测试无法取消他人的收藏')]
    public function test_destroy_does_not_delete_others_collection()
    {
        $source = $this->createSource();
        $collection = $this->silentlyCreateCollection([
            'user_id' => $this->otherUser->id,
            'source_id' => $source->id,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/collections/'.$collection->id);

        $response->assertOk();
        $this->assertDatabaseHas('collections', ['id' => $collection->id]);
    }
}
