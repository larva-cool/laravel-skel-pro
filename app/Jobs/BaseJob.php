<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

namespace App\Jobs;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

/**
 * 任务基类
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class BaseJob implements ShouldQueue
{
    use Queueable;

    /**
     * 作业在超时前可以运行的秒数。
     *
     * @var int
     */
    public int $timeout = 120;
    
    /**
     * 任务最大尝试次数
     */
    public function tries(): int
    {
        return 30;
    }

    /**
     * 确定作业超时时间。
     * @return DateTime
     */
    public function retryUntil(): DateTime
    {
        // 根据尝试次数动态调整
        $minutes = $this->attempts() * 10; // 每次尝试多给10分钟

        return Carbon::now()->plus(minutes: $minutes);
    }

    /**
     * 计算重试延迟时间（每次重试 + 30秒）。
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [
            $this->attempts() * 30, // 第一次重试：30秒，第二次：60秒，第三次：90秒...
        ];
    }

}
