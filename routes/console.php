<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Jobs\User\StatUserJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 用户统计 每天夜里1点开始
Schedule::job(new StatUserJob)->dailyAt('1:00')->onOneServer();

// 清理模型 0 点
Schedule::command('model:prune')->daily()->onOneServer();

// 队列健康指标 5分钟
Schedule::command('horizon:snapshot')->everyFiveMinutes()->onOneServer();

if (! app()->isProduction()) {
    // 0 点
    Schedule::command('telescope:prune --hours=24')->daily()->onOneServer();
}
