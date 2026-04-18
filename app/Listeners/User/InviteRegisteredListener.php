<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Listeners\User;

use App\Events\User\InviteRegistered;
use App\Support\UserHelper;

/**
 * 邀请注册事件监听器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class InviteRegisteredListener
{
    /**
     * Handle the event.
     */
    public function handle(InviteRegistered $event): void
    {
        // 处理邀请注册事件
        UserHelper::connectInvite($event->user, $event->inviteCode);
    }
}
