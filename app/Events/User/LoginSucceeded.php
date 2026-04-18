<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Events\User;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * 登录成功
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class LoginSucceeded implements ShouldBroadcast
{
    use Dispatchable,InteractsWithQueue, InteractsWithSockets, SerializesModels;

    /**
     * The authenticated user.
     *
     * @var Authenticatable
     */
    public $user;

    /**
     * The user ip.
     *
     * @var string
     */
    public $ip;

    /**
     * The user ip port.
     *
     * @var string
     */
    public $port;

    /**
     * The user agent.
     */
    public string $userAgent;

    /**
     * Create a new event instance.
     *
     * @param  Authenticatable  $user
     * @param  string  $ip
     * @param  int  $port
     * @param  string  $ua
     * @return void
     */
    public function __construct($user, $ip, $port, $userAgent)
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->port = $port;
        $this->userAgent = $userAgent;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('User.'.$this->user->id),
        ];
    }
}
