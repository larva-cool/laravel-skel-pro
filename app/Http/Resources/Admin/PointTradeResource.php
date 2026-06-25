<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Point\PointTrade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 积分记录
 *
 * @mixin PointTrade
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PointTradeResource extends JsonResource
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
            'user_name' => $this->user?->name,
            'user_avatar' => $this->user?->avatar,
            'points' => $this->points,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
