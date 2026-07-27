<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Task\TaskGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 任务分组资源
 *
 * @mixin TaskGroup
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class TaskGroupResource extends JsonResource
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
            'name' => $this->name,
            'tasks_count' => $this->tasks_count,
            'description' => $this->description,
            'completed_count' => $this->log_count,
            'order' => $this->order,
            'type' => $this->type,
            'type_name' => $this->type_name,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'show_url' => route('admin.task_groups.show', $this->id),
            'tasks_url' => route('admin.task_groups.tasks.index', ['task_group' => $this]),
            'status_url' => route('admin.task_groups.status', ['task_group' => $this]),
            'edit_url' => route('admin.task_groups.edit', $this->id),
            'update_url' => route('admin.task_groups.update', $this->id),
            'delete_url' => route('admin.task_groups.destroy', $this->id),
        ];
    }
}
