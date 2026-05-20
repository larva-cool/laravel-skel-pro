<?php

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
    protected $description = '生成系统统计数据';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 禁用Telescope
        disable_telescope();

        $yesterday = Carbon::yesterday();
        $this->output->info("开始统计 {$yesterday->toDateString()} 的用户注册数量和活跃数量...");
        StatUserJob::dispatch($yesterday->toDateString());
    }
}
