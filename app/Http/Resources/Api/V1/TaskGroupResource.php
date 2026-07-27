<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Task\TaskGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 任务分组资源
 *
 * @mixin TaskGroup
 *
 * @author Tongle Xu <xutongle@gmail.com>
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
            'description' => $this->description,
            'type' => $this->type->value,
            'type_name' => $this->type_name,
            'order' => $this->order,
            'tasks' => TaskResource::collection($this->whenLoaded('activeTasks')),
        ];
    }
}
