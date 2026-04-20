<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\System\Area;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Area
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AreaResource extends JsonResource
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
            'value' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'area_code' => $this->area_code,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'city_code' => $this->city_code,
            'icon' => 'layui-icon layui-icon-set',
            'order' => $this->order,
            'is_parent' => $this->children_count > 0,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.areas.edit', ['area' => $this]),
            'update_url' => route('admin.areas.update', ['area' => $this]),
            'delete_url' => route('admin.areas.destroy', ['area' => $this]),
        ];
    }
}
