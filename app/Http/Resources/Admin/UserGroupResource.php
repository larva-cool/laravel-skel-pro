<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\User\UserGroup;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 用户组资源
 *
 * @mixin UserGroup
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'desc' => $this->desc,
            'show_toolbar' => $this->id != 1,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.user_groups.edit', $this->id),
            'delete_url' => route('admin.user_groups.destroy', $this->id),
        ];
    }
}
