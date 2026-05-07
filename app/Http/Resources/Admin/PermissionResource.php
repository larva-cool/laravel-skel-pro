<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\System\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 权限资源
 *
 * @mixin Permission
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
            'display_name' => $this->display_name,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.permissions.edit', $this->id),
            'update_url' => route('admin.permissions.update', $this->id),
            'delete_url' => route('admin.permissions.destroy', $this->id),
        ];
    }
}
