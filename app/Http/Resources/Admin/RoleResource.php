<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 角色资源
 *
 * @mixin \Spatie\Permission\Models\Role
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'role_id' => $this->id,
            'role_name' => $this->name,
            'role_code' => $this->name,
            'description' => '',
            'enabled' => true,
            'create_time' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
