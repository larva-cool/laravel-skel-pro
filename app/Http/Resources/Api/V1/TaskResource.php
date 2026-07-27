<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Task\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 任务资源
 *
 * @mixin Task
 *
 * @author Tongle Xu <xutongle@gmail.com>
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
            'type' => $this->type->value,
            'type_name' => $this->type_name,
            'coins' => $this->coins,
            'activity_bonus' => (bool) $this->activity_bonus->value,
            'description' => $this->description,
            'condition' => $this->condition,
            'order' => $this->order,
            'log_count' => $this->log_count,
            'is_completed_today' => $this->relationLoaded('todayLog')
                ? ($this->todayLog !== null && $this->todayLog->trade_id !== null)
                : false,
        ];
    }
}
