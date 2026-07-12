<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Notifications\Content;

use App\Models\Content\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * 被 @提及 通知
 *
 * 当评论中包含 @某用户 时触发此通知，仅通过数据库通道投递站内信。
 *
 * @property-read Comment $comment 被 @的评论实例
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class MentionedInComment extends Notification
{
    use Queueable;

    /** @var Comment 被 @的评论实例 */
    protected Comment $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * 判断通知是否发送给指定用户（只有被 @的用户才会收到）.
     */
    public function shouldSendTo($notifiable, string $channel): bool
    {
        return in_array($notifiable->getKey(), $this->comment->mentioned_users ?? [], true);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'content' => $this->comment->content,
            'user_id' => $this->comment->user_id,
            'message' => '您被 @提及在一条评论中',
        ];
    }
}
