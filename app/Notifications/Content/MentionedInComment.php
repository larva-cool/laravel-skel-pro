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
 * @author Tongle Xu <xutongle@gmail.com>
 */
class MentionedInComment extends Notification
{
    use Queueable;

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
            'message' => 'You were mentioned in a comment',
        ];
    }
}
