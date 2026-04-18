<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Content\Follow;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\morphMany;

/**
 * 关注
 *
 * @mixin Model
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
trait HasFollowable
{
    /**
     * 关注
     */
    public function followers(): morphMany
    {
        return $this->morphMany(Follow::class, 'source');
    }

    /**
     * 获取指定的指定关注
     */
    public function getFollow(User|string|int $user)
    {
        if ($user instanceof User) {
            return $this->followers()->where('user_id', $user->id)->first();
        }

        return $this->followers()->where('user_id', (int) $user)->first();
    }
}
