<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 用户资源
 *
 * @mixin User
 *
 * @author Tongle Xu <xutongle@msn.com>
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
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'socket_id' => $this->socket_id,
            'gender' => $this->profile->gender,
            'birthday' => $this->profile->birthday,
            'province_id' => $this->profile->province_id,
            'province_name' => $this->profile?->province_name,
            'city_id' => $this->profile->city_id,
            'city_name' => $this->profile?->city_name,
            'district_id' => $this->profile->district_id,
            'district_name' => $this->profile?->district_name,
            'available_points' => $this->available_points,
            'available_coins' => $this->available_coins,
            'referrer_id' => $this->extra->referrer_id,
            'website' => $this->profile->website,
            'intro' => $this->profile->intro,
            'bio' => $this->profile->bio,
            'invite_code' => $this->extra->invite_code,
            'invite_registered_count' => $this->extra->invite_registered_count,
            'username_change_count' => $this->extra->username_change_count,
            'login_count' => $this->extra->login_count,
            'last_login_ip' => $this->extra->last_login_ip,
            'first_active_at' => $this->extra->first_active_at?->toDateTimeString(),
            'last_active_at' => $this->extra->last_active_at?->toDateTimeString(),
            'last_login_at' => $this->extra->last_login_at?->toDateTimeString(),
            'phone_verified_at' => $this->extra->phone_verified_at?->toDateTimeString(),
            'email_verified_at' => $this->extra->email_verified_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
            'edit_url' => route('admin.users.edit', $this->id),
            'update_url' => route('admin.users.update', $this->id),
            'delete_url' => route('admin.users.destroy', $this->id),
        ];
    }
}
