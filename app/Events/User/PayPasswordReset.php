<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Events\User;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

/**
 * 用户支付密码重置事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PayPasswordReset
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Authenticatable  $user  The user.
     */
    public function __construct(
        public $user,
    ) {}
}
