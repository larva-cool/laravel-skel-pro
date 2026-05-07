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

// 清理模型 凌晨 2 点
Schedule::command('model:prune')->dailyAt('2:00')->onOneServer();

// 统计相关
// 队列健康指标 5分钟一次
Schedule::command('horizon:snapshot')->everyFiveMinutes()->onOneServer();
// 用户统计 每天夜里1点开始
Schedule::job(new StatUserJob)->dailyAt('1:00')->onOneServer();




if (! app()->isProduction()) {
    // 0 点
    Schedule::command('telescope:prune --hours=24')->daily()->onOneServer();
}

// 每月 1 号凌晨 00:00 执行
Schedule::command('db:create-partition')->monthly();
