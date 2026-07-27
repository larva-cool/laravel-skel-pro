<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Feedback\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 反馈资源（后台）
 *
 * @mixin Feedback
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class FeedbackResource extends JsonResource
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
            'user_name' => $this->user->name ?? '',
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'contact' => $this->contact,
            'attachments' => $this->attachments ?: [],
            'status' => $this->status,
            'reply' => $this->reply,
            'handled_by' => $this->handled_by,
            'handled_at' => $this->handled_at?->toDateTimeString(),
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.feedbacks.edit', ['feedback' => $this->id]),
            'update_url' => route('admin.feedbacks.update', ['feedback' => $this->id]),
            'delete_url' => route('admin.feedbacks.destroy', ['feedback' => $this->id]),
        ];
    }
}
