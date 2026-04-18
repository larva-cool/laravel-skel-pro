<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Enum\ReviewStatus;
use App\Models\Content\Comment;
use App\Models\User;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(CommentPolicy::class)]
#[TestDox('评论策略测试')]
class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试用户查看所有评论')]
    public function test_view_any()
    {
        $policy = new CommentPolicy;
        $user = User::factory()->create();

        // 验证任何用户都可以查看评论列表
        $this->assertTrue($policy->viewAny($user));
    }

    #[Test]
    #[TestDox('测试用户查看评论')]
    public function test_view()
    {
        $policy = new CommentPolicy;
        $user = User::factory()->create();
        $ownComment = Comment::factory()->create(['user_id' => $user->id]);
        $otherComment = Comment::factory()->create(['status' => ReviewStatus::APPROVED]);

        // 验证用户可以查看自己的评论
        $this->assertTrue($policy->view($user, $ownComment));

        // 验证用户可以查看他人的评论
        $this->assertTrue($policy->view($user, $otherComment));
    }

    #[Test]
    #[TestDox('测试用户创建评论')]
    public function test_create()
    {
        $policy = new CommentPolicy;
        $user = User::factory()->create();

        // 验证任何用户都可以创建评论
        $this->assertTrue($policy->create($user));
    }

    #[Test]
    #[TestDox('测试用户更新评论')]
    public function test_update()
    {
        $policy = new CommentPolicy;
        $user = User::factory()->create();
        $ownComment = Comment::factory()->create(['user_id' => $user->id]);
        $otherComment = Comment::factory()->create();

        // 验证用户可以更新自己的评论
        $this->assertTrue($policy->update($user, $ownComment));

        // 验证用户不能更新他人的评论
        $this->assertFalse($policy->update($user, $otherComment));
    }

    #[Test]
    #[TestDox('测试用户删除评论')]
    public function test_delete()
    {
        $policy = new CommentPolicy;
        $user = User::factory()->create();
        $ownComment = Comment::factory()->create(['user_id' => $user->id]);
        $otherComment = Comment::factory()->create();

        // 验证用户可以删除自己的评论
        $this->assertTrue($policy->delete($user, $ownComment));

        // 验证用户不能删除他人的评论
        $this->assertFalse($policy->delete($user, $otherComment));
    }
}
