<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Enums\ReviewStatus;
use App\Models\Content\Comment;
use App\Models\User;
use App\Notifications\Content\MentionedInComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\DatabaseNotification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(MentionedInComment::class)]
#[TestDox('被 @提及通知测试')]
class MentionedInCommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 前置准备：注册 morph map，确保 Comment 多态关联使用 'comment' 键。
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    #[TestDox('验证通知通过数据库通道投递')]
    public function test_notification_uses_database_channel(): void
    {
        $comment = Comment::factory()->create();
        $notification = new MentionedInComment($comment);

        $channels = $notification->via(new User);

        $this->assertEquals(['database'], $channels);
    }

    #[Test]
    #[TestDox('验证 toArray 返回正确的数据结构')]
    public function test_to_array_returns_correct_structure(): void
    {
        $comment = Comment::factory()->create([
            'id' => 42,
            'content' => 'This is a test comment @mention',
            'user_id' => 100,
        ]);
        $notification = new MentionedInComment($comment);
        $notifiable = new User;

        $data = $notification->toArray($notifiable);

        $this->assertEquals([
            'comment_id' => 42,
            'content' => 'This is a test comment @mention',
            'user_id' => 100,
            'message' => '您被 @提及在一条评论中',
        ], $data);
    }

    #[Test]
    #[TestDox('验证 shouldSendTo 正确判断被 @的用户')]
    public function test_should_send_to_when_user_is_mentioned(): void
    {
        $comment = Comment::factory()->create([
            'mentioned_users' => [1, 2, 3],
        ]);
        $notification = new MentionedInComment($comment);

        // 用户 ID 为 2 的被 @提及
        $fakeUser = $this->createFakeNotifiable(2);
        $this->assertTrue($notification->shouldSendTo($fakeUser, 'database'));

        // 用户 ID 为 5 的未被 @提及
        $otherUser = $this->createFakeNotifiable(5);
        $this->assertFalse($notification->shouldSendTo($otherUser, 'database'));
    }

    /**
     * 创建带有指定 key 的假 notifiable 对象。
     */
    private function createFakeNotifiable(int $key): object
    {
        return new class($key)
        {
            public function __construct(private readonly int $key) {}

            public function getKey()
            {
                return $this->key;
            }
        };
    }

    #[Test]
    #[TestDox('验证 shouldSendTo 正确处理空 mentioned_users')]
    public function test_should_not_send_when_no_users_mentioned(): void
    {
        $comment = Comment::factory()->create([
            'mentioned_users' => [],
        ]);
        $notification = new MentionedInComment($comment);

        $user = User::factory()->create();
        $this->assertFalse($notification->shouldSendTo($user, 'database'));
    }

    #[Test]
    #[TestDox('验证标记已审核时 @提及的用户收到通知')]
    public function test_mark_approved_sends_notifications_to_mentioned_users(): void
    {
        $author = User::factory()->create();
        $mentionedUser1 = User::factory()->create();
        $mentionedUser2 = User::factory()->create();
        $unmentionedUser = User::factory()->create();

        // 使用 Comment 作为源（评论回复场景）
        $parentComment = Comment::factory()->create(['id' => 1]);

        $comment = Comment::factory()->create([
            'user_id' => $author->id,
            'source_id' => $parentComment->id,
            'source_type' => get_class($parentComment),
            'status' => ReviewStatus::PENDING,
            'mentioned_users' => [$mentionedUser1->id, $mentionedUser2->id],
        ]);

        $comment->markApproved();

        // 被 @提及的用户应收到通知
        $notification1 = DatabaseNotification::where('type', MentionedInComment::class)
            ->where('notifiable_type', 'user')
            ->where('notifiable_id', $mentionedUser1->id)
            ->first();

        $this->assertNotNull($notification1);
        $this->assertEquals($comment->id, $notification1->data['comment_id']);

        $notification2 = DatabaseNotification::where('type', MentionedInComment::class)
            ->where('notifiable_type', 'user')
            ->where('notifiable_id', $mentionedUser2->id)
            ->first();

        $this->assertNotNull($notification2);

        // 未被 @提及的用户不应收到通知
        $unmentionedNotification = DatabaseNotification::where('type', MentionedInComment::class)
            ->where('notifiable_type', 'user')
            ->where('notifiable_id', $unmentionedUser->id)
            ->first();

        $this->assertNull($unmentionedNotification);
    }
}
