<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Observers;

use App\Models\User\UserProfile;

/**
 * 用户个人信息模型观察者
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserProfileObserver
{
    /**
     * Handle the UserProfile "created" event.
     */
    public function created(UserProfile $userProfile): void
    {
        //
    }

    /**
     * Handle the UserProfile "saving" event.
     */
    public function saving(UserProfile $userProfile): void
    {
        //
    }

    /**
     * Handle the UserProfile "updated" event.
     */
    public function updated(UserProfile $userProfile): void
    {
        //
    }

    /**
     * Handle the UserProfile "deleted" event.
     */
    public function deleted(UserProfile $userProfile): void
    {
        //
    }
}
