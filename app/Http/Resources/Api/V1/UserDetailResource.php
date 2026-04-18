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
 * 用户详细信息
 *
 * @mixin User
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserDetailResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'is_vip' => $this->isVip(),
            'available_points' => $this->available_points,
            'available_coins' => $this->available_coins,
            'gender' => $this->profile->gender,
            'birthday' => $this->profile->birthday?->toDateString(),
            'province_id' => $this->profile->province_id,
            'city_id' => $this->profile->city_id,
            'district_id' => $this->profile->district_id,
            'website' => $this->profile->website,
            'intro' => $this->profile->intro,
            'bio' => $this->profile->bio,
            'invite_code' => $this->extra->invite_code,
            'vip_expires_at' => $this->vip_expires_at?->toDateTimeString(),
            'register_time' => $this->created_at->toDateTimeString(),
        ];
    }
}
