<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Jobs\User;

use App\Models\PersonalAccessToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * 删除指定的 Token
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class DeleteAccessTokenJob implements ShouldQueue
{
    use Queueable;

    public string $token;

    /**
     * Create a new job instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PersonalAccessToken::where('token', $this->token)->delete();
    }
}
