<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\System\Dict;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 字典
 *
 * @mixin Dict
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class DictResource extends JsonResource
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
            'code' => $this->code,
            'status' => $this->status,
            'order' => $this->order,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.dicts.edit', $this->id),
            'edit_data_url' => route('admin.dicts.edit_data', $this->id),
            'update_url' => route('admin.dicts.update', $this->id),
            'delete_url' => route('admin.dicts.destroy', $this->id),
        ];
    }
}
