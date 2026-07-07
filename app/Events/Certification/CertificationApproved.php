<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Events\Certification;

use App\Models\Certification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * 实名认证通过事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CertificationApproved implements ShouldBroadcast, ShouldHandleEventsAfterCommit, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithSockets, SerializesModels;

    /**
     * The certification.
     */
    public Certification $certification;

    /**
     * Create a new event instance.
     */
    public function __construct(Certification $certification)
    {
        $this->certification = $certification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ucfirst($this->certification->certifiable_type).'.'.$this->certification->certifiable_id),
        ];
    }
}
