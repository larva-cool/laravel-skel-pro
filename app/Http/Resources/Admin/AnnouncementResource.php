<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Announcement\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 公告资源
 *
 * @mixin Announcement
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AnnouncementResource extends JsonResource
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
            'coverage' => $this->coverage,
            'title' => $this->title,
            'image' => $this->image,
            'status' => $this->status,
            'read_count' => $this->read_count,
            'effective_time_type' => $this->effective_time_type,
            'effective_start_time' => $this->effective_start_time?->toDateTimeString(),
            'effective_end_time' => $this->effective_end_time?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.announcements.edit', $this->id),
            'update_url' => route('admin.announcements.update', $this->id),
            'delete_url' => route('admin.announcements.destroy', $this->id),
        ];
    }
}
