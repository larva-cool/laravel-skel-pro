<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Jobs\Task;

use App\Models\Task\Task;
use App\Models\Task\TaskGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * 修复任务数
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RepairLogCountJob implements ShouldQueue
{
    use Queueable;

    /**
     * 作业在超时前可以运行的秒数。
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int|string $id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $task = Task::query()->where('id', $this->id)->first();
        $task->repairLogCount();

        $logCount = Task::query()->where('group_id', $task->group_id)->sum('log_count');
        TaskGroup::query()->where('id', $task->group_id)->update(['log_count' => $logCount]);
    }
}
