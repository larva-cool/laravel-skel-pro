<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Admin\AdminPermission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 权限资源
 *
 * @mixin AdminPermission
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PermissionResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'is_parent' => $this->children_count > 0,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.permissions.edit', $this->id),
            'update_url' => route('admin.permissions.update', $this->id),
            'delete_url' => route('admin.permissions.destroy', $this->id),
        ];
    }
}
