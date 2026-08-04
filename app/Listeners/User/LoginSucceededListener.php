<?php

namespace App\Listeners\User;

use App\Events\User\LoginSucceeded;
use Illuminate\Support\Carbon;

/**
 * 登录成功时间监听器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class LoginSucceededListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LoginSucceeded $event): void
    {
        // 记录登录历史
        $event->user->loginHistories()->create([
            'ip' => $event->ip,
            'port' => $event->port,
            'user_agent' => $event->userAgent,
            'login_at' => Carbon::now(),
        ]);
    }
}
