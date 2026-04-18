<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 用户资源
 *
 * @mixin User
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UserResource extends JsonResource
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
            'avatar' => $this->avatar,
            'is_vip' => $this->isVip(),
            'vip_expires_at' => $this->vip_expires_at?->toDateTimeString(),
            'register_time' => $this->created_at->toDateTimeString(),
        ];
    }
}
