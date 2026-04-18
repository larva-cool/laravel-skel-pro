<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * 用户模型观察者
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "saving" event.
     */
    public function saving(User $user): void
    {
        $user->email = $user->email ?? null;
        $user->username = $user->username ?? $user->email;
        $user->name = $user->name ?? $user->username;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->profile()->create();
        $user->extra()->create();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // 释放用户名、手机号、邮箱
        $user->extra->updateQuietly(['restore_data' => [
            'phone' => $user->phone,
            'email' => $user->email,
            'username' => $user->username,
        ]]);
        $user->updateQuietly(['username' => null, 'phone' => null, 'email' => null]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $restoreData = $user->extra->restore_data;
        $user->updateQuietly([
            'username' => $restoreData['username'],
            'phone' => $restoreData['phone'],
            'email' => $restoreData['email'],
        ]);
        $user->extra->updateQuietly(['restore_data' => null]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $user->loginHistories()->delete();
        // $user->signs()->delete();
        $user->points()->delete();
        $user->coins()->delete();
        $user->addresses()->delete();
        // $user->collections()->delete();
        // $user->likes()->delete();
        $user->socials()->delete();
        $user->profile->delete();
        $user->extra->delete();
    }
}
