<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enum\ReviewStatus;
use App\Http\Controllers\Api\V1\CommentController;
use App\Models\Content\Comment;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 评论控制器测试类
 *
 * 包含评论的CRUD操作、点赞、回复等功能的测试
 */
#[CoversClass(CommentController::class)]
#[TestDox('评论控制器测试')]
class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var Authenticatable 测试用户 */
    protected $user;

    /** @var Authenticatable 另一个测试用户 */
    protected $anotherUser;

    /**
     * 测试前置方法
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 手动创建测试用户
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->anotherUser = User::create([
            'name' => 'Another Test User',
            'email' => 'another@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * 创建测试评论的辅助方法
     *
     * @param  array  $attributes  评论属性
     */
    protected function createComment(array $attributes = []): Comment
    {
        return Comment::create(array_merge([
            'user_id' => $this->user->id,
            'source_id' => 1,
            'source_type' => 'comment',
            'content' => 'Test comment content',
            'status' => ReviewStatus::PENDING,
        ], $attributes));
    }

    /**
     * 测试需要身份验证
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试需要身份验证')]
    public function test_authentication_required()
    {
        // 未登录用户尝试创建评论
        $response = $this->postJson('/api/v1/comments', []);
        $response->assertStatus(401);

        // 未登录用户尝试删除评论
        $response = $this->deleteJson('/api/v1/comments/9999991');
        $response->assertStatus(401);
    }

    /**
     * 测试获取评论列表
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试获取评论列表')]
    public function test_index()
    {
        // 创建测试评论
        for ($i = 0; $i < 3; $i++) {
            $this->createComment([
                'content' => '测试评论 '.$i,
            ]);
        }

        // 创建不同source_type的评论
        $this->createComment([
            'source_id' => 2,
            'source_type' => 'comment',
            'content' => '帖子评论',
        ]);

        // 测试获取特定source_type和source_id的评论
        $response = $this->actingAs($this->user)->getJson('/api/v1/comment/1/comments');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'user_name',
                    'user_avatar',
                    'content',
                    'created_at',
                ],
            ],
            'links',
            'meta',
        ]);
        $this->assertCount(3, $response->json('data')); // 应该返回3个article评论

        // 测试获取不同source_id的评论
        $response = $this->actingAs($this->user)->getJson('/api/v1/comment/11/comments');
        $this->assertCount(0, $response->json('data'));

        // 测试获取不同source_type的评论
        $response = $this->actingAs($this->user)->getJson('/api/v1/comment/2/comments');
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * 测试评论排序
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试评论排序')]
    public function test_index_sorting()
    {
        // 创建测试评论
        $comment1 = $this->createComment(['content' => '评论1']);
        sleep(1); // 确保创建时间有差异
        $comment2 = $this->createComment(['content' => '评论2']);
        sleep(1);
        $comment3 = $this->createComment(['content' => '评论3']);

        // 获取评论列表
        $response = $this->actingAs($this->user)->getJson('/api/v1/comment/1/comments');

        // 验证评论按ID降序排列
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertEquals($comment3->id, $data[0]['id']);
        $this->assertEquals($comment2->id, $data[1]['id']);
        $this->assertEquals($comment1->id, $data[2]['id']);
    }

    /**
     * 测试创建评论
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试创建评论')]
    public function test_store()
    {
        $data = [
            'source_id' => 1,
            'source_type' => 'comment',
            'content' => '测试评论内容',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/comments', $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'user_id',
            'user_name',
            'user_avatar',
            'content',
            'created_at',
        ]);
        $this->assertEquals($this->user->id, $response->json('user_id'));
        $this->assertEquals($data['content'], $response->json('content'));

        // 验证数据库中是否存在该评论
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'content' => $data['content'],
            'source_id' => 1,
            'source_type' => 'comment',
        ]);
    }

    /**
     * 测试创建评论的参数验证
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试创建评论的参数验证')]
    public function test_store_validation()
    {
        // 测试缺少必要参数
        $response = $this->actingAs($this->user)->postJson('/api/v1/comments', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_id', 'source_type', 'content']);

        // 测试无效的source_type
        $data = [
            'source_id' => 1,
            'source_type' => 'invalid_type',
            'content' => '测试评论内容',
        ];
        $response = $this->actingAs($this->user)->postJson('/api/v1/comments', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_type']);

        // 测试content为空
        $data = [
            'source_id' => 1,
            'source_type' => 'comment',
            'content' => '',
        ];
        $response = $this->actingAs($this->user)->postJson('/api/v1/comments', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);

        // 测试content太长
        $data = [
            'source_id' => 1,
            'source_type' => 'comment',
            'content' => str_repeat('a', 1001),
        ];
        $response = $this->actingAs($this->user)->postJson('/api/v1/comments', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /**
     * 测试回复评论
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试回复评论')]
    public function test_reply_to_comment()
    {
        // 创建父评论
        $parentComment = $this->createComment([
            'content' => '父评论',
        ]);

        // 回复父评论
        $data = [
            'source_id' => $parentComment->id,
            'source_type' => 'comment',
            'content' => '回复评论',
        ];

        // 使用类型转换确保正确传递 Authenticatable 接口实例
        $response = $this->actingAs($this->anotherUser)->postJson('/api/v1/comments', $data);
        $response->assertStatus(201);

        // 验证回复是否正确创建
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->anotherUser->id,
            'source_id' => $parentComment->id,
            'content' => '回复评论',
        ]);
    }

    /**
     * 测试点赞评论
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试点赞评论')]
    public function test_like()
    {
        // 创建测试评论
        $comment = $this->createComment([
            'user_id' => $this->anotherUser->id,
            'content' => '测试评论',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/likes', [
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('likes', [
            'user_id' => $this->user->id,
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        // 验证点赞数是否增加
        $comment->refresh();
        $this->assertEquals(1, $comment->like_count);
    }

    /**
     * 测试重复点赞评论
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试重复点赞评论')]
    public function test_like_duplicate()
    {
        // 创建测试评论
        $comment = $this->createComment([
            'user_id' => $this->anotherUser->id,
            'content' => '测试评论',
        ]);

        // 第一次点赞
        $this->actingAs($this->user)->postJson('/api/v1/likes', [
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        // 第二次点赞（应该失败）
        $response = $this->actingAs($this->user)->postJson('/api/v1/likes', [
            'source_id' => $comment->id,
            'source_type' => 'comment',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => trans('system.like_exist')]);

        // 验证点赞数没有重复增加
        $comment->refresh();
        $this->assertEquals(1, $comment->like_count);
    }

    /**
     * 测试删除评论
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试删除评论')]
    public function test_destroy()
    {
        // 创建测试评论
        $comment = $this->createComment([
            'content' => '测试评论',
        ]);

        $response = $this->actingAs($this->user)->deleteJson('/api/v1/comments/'.$comment->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * 测试删除他人评论（应该失败）
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试删除他人评论')]
    public function test_destroy_others_comment()
    {
        // 创建他人评论
        $comment = $this->createComment([
            'user_id' => $this->anotherUser->id,
            'content' => '他人评论',
        ]);

        // 尝试删除他人评论
        $response = $this->actingAs($this->user)->deleteJson('/api/v1/comments/'.$comment->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => '他人评论',
        ]);
    }

    /**
     * 测试评论分页功能
     *
     * @return void
     */
    #[Test]
    #[TestDox('测试评论分页功能')]
    public function test_index_pagination()
    {
        // 创建15条测试评论
        for ($i = 0; $i < 15; $i++) {
            $this->createComment([
                'content' => '测试评论 '.$i,
            ]);
        }

        // 默认应该返回10条评论
        $response = $this->actingAs($this->user)->getJson('/api/v1/comment/1/comments');
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(10, $data);
        $this->assertNotNull($response->json('links.next'));
        $this->assertNull($response->json('links.prev'));

        // 测试自定义每页数量
        $response = $this->actingAs($this->user)->getJson('/api/v1/comment/1/comments?per_page=5');
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(5, $data);

        // 测试第二页
        $response = $this->actingAs($this->user)->getJson('/api/v1/comment/1/comments?page=2');
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(5, $data);
        $this->assertNotNull($response->json('links.prev'));
    }
}
