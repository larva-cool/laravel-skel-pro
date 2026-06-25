<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\User\StatUserJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * 数据统计
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '数据统计';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 禁用Telescope
        disable_telescope();

        $date = Carbon::yesterday();
        $this->output->info("开始统计 {$date->toDateString()} 的用户注册数量和活跃数量。");
        StatUserJob::dispatch($date->toDateString());

    }
}
