<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Content\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 评论资源
 *
 * @mixin Comment
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
            'user_avatar' => $this->user->avatar,
            'comment_count' => $this->comment_count,
            'like_count' => $this->like_count,
            'source_id' => $this->source_id,
            'content' => $this->content,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
