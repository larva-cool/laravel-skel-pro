<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Http\Controllers\Api\V1\CollectionController;
use App\Models\Content\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 收藏控制器测试类
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
        Collection::create([
            'user_id' => $this->user->id,
            'source_id' => 100,
            'source_type' => 'comment',
        ]);
        Collection::create([
            'user_id' => $this->otherUser->id,
            'source_id' => 101,
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
        Collection::create([
            'user_id' => $this->user->id,
            'source_id' => 100,
            'source_type' => 'comment',
        ]);
        Collection::create([
            'user_id' => $this->user->id,
            'source_id' => 200,
            'source_type' => 'user',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/collections?type=user');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('user', $response->json('data.0.source_type'));
    }

    #[Test]
    #[TestDox('测试添加收藏成功')]
    public function test_store_creates_collection()
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/collections', [
            'source_id' => $target->id,
            'source_type' => 'user',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => trans('system.collection_success')]);
        $this->assertDatabaseHas('collections', [
            'user_id' => $this->user->id,
            'source_id' => $target->id,
            'source_type' => 'user',
        ]);
    }

    #[Test]
    #[TestDox('测试重复收藏不会创建重复记录')]
    public function test_store_duplicate_does_not_create_new()
    {
        Collection::create([
            'user_id' => $this->user->id,
            'source_id' => 500,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/collections', [
            'source_id' => 500,
            'source_type' => 'comment',
        ]);

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
        $collection = Collection::create([
            'user_id' => $this->user->id,
            'source_id' => 300,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/collections/'.$collection->id);

        $response->assertOk();
        $response->assertJson(['message' => trans('system.collection_cancel_success')]);
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }

    #[Test]
    #[TestDox('测试无法取消他人的收藏')]
    public function test_destroy_does_not_delete_others_collection()
    {
        $collection = Collection::create([
            'user_id' => $this->otherUser->id,
            'source_id' => 400,
            'source_type' => 'comment',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/collections/'.$collection->id);

        $response->assertOk();
        $this->assertDatabaseHas('collections', ['id' => $collection->id]);
    }
}
