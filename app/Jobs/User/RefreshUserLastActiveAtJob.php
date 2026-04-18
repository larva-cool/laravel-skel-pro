<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Jobs\User;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

/**
 * 刷新最后活动时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class RefreshUserLastActiveAtJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * The user.
     */
    protected User $user;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): ?int
    {
        return $this->user?->id;
    }

    /**
     * Determine number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 2;
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        $minutes = $this->attempts() * 15;

        return Carbon::now()->addSeconds($minutes);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->refreshLastActiveAt();
    }
}
