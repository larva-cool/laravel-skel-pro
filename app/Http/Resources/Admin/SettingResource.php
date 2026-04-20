<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\System\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 设置项资源
 *
 * @mixin Setting
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class SettingResource extends JsonResource
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
            'key' => $this->key,
            'value' => $this->value,
            'cast_type' => $this->cast_type,
            'order' => $this->order,
            'remark' => $this->remark,
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.settings.edit', $this->id),
            'delete_url' => route('admin.settings.destroy', $this->id),
        ];
    }
}
