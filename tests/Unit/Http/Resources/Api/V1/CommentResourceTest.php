<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Content\Comment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(CommentResource::class)]
#[TestDox('CommentResource 测试')]
class CommentResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    #[TestDox('测试资源结构是否正确')]
    public function test_resource_structure()
    {
        $comment = $this->createComment();
        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request);

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('user_name', $array);
        $this->assertArrayHasKey('user_avatar', $array);
        $this->assertArrayHasKey('comment_count', $array);
        $this->assertArrayHasKey('like_count', $array);
        $this->assertArrayHasKey('source_id', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('created_at', $array);
    }

    #[Test]
    #[TestDox('测试日期格式是否正确')]
    public function test_date_format()
    {
        $date = Carbon::parse('2023-01-01 12:00:00');
        $comment = $this->createComment(['created_at' => $date]);
        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request);

        $this->assertEquals('2023-01-01 12:00:00', $array['created_at']);
    }

    #[Test]
    #[TestDox('测试null日期处理')]
    public function test_null_date_handling()
    {
        $comment = $this->createComment(['created_at' => null]);
        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request);

        $this->assertNull($array['created_at']);
    }

    #[Test]
    #[TestDox('测试字段值映射是否正确')]
    public function test_field_value_mapping()
    {
        $user = $this->createUser();
        $comment = $this->createComment([
            'id' => 1,
            'user_id' => $user->id,
            'content' => 'Test comment content',
            'source_id' => 10,
            'comment_count' => 5,
            'like_count' => 10,
        ]);
        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request);

        $this->assertSame(1, $array['id']);
        $this->assertSame(1, $array['user_id']);
        $this->assertSame('Test User', $array['user_name']);
        $this->assertSame('https://example.com/avatar.jpg', $array['user_avatar']);
        $this->assertSame(5, $array['comment_count']);
        $this->assertSame(10, $array['like_count']);
        $this->assertSame(10, $array['source_id']);
        $this->assertSame('Test comment content', $array['content']);
    }

    /**
     * 创建模拟的用户对象
     */
    private function createUser()
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('Test User');
        $user->shouldReceive('getAttribute')->with('avatar')->andReturn('https://example.com/avatar.jpg');
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);

        return $user;
    }

    /**
     * 创建模拟的评论对象
     */
    private function createComment(array $attributes = [])
    {
        // 设置默认值
        $defaults = [
            'id' => 1,
            'user_id' => 1,
            'content' => 'Test comment',
            'source_id' => 1,
            'source_type' => 'article',
            'comment_count' => 0,
            'like_count' => 0,
            'created_at' => Carbon::now(),
        ];

        // 合并默认值和传入的属性
        $attributes = array_merge($defaults, $attributes);

        // 创建模拟对象
        $comment = \Mockery::mock(Comment::class);

        // 设置 exists 属性为 true 以绕过 MassAssignmentException
        $comment->shouldReceive('__get')->with('exists')->andReturn(true);

        // 模拟属性获取
        foreach ($attributes as $key => $value) {
            $comment->shouldReceive('getAttribute')->with($key)->andReturn($value);
        }

        // 模拟用户关联
        $user = $this->createUser();
        $comment->shouldReceive('getAttribute')->with('user')->andReturn($user);
        $comment->shouldReceive('user')->andReturn($user);

        return $comment;
    }

    /**
     * 清理模拟对象
     */
    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
