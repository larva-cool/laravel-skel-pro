<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Task\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 任务资源
 *
 * @mixin Task
 */
class TaskResource extends JsonResource
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
            'type' => $this->type,
            'type_name' => $this->type_name,
            'coins' => $this->coins,
            'order' => $this->order,
            'activity_bonus' => $this->activity_bonus,
            'log_count' => $this->log_count,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.task_groups.tasks.edit', ['task_group' => $this->group, 'task' => $this]),
            'delete_url' => route('admin.task_groups.tasks.destroy', ['task_group' => $this->group, 'task' => $this]),
        ];
    }
}
