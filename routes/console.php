<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// 清理模型 凌晨 2 点
Schedule::command('model:prune')->dailyAt('2:00')->onOneServer();

// 统计相关
Schedule::command('app:stat')->dailyAt('1:00')->onOneServer();// 系统统计 每天夜里1点开始

// 队列健康指标 5分钟一次
Schedule::command('horizon:snapshot')->everyFiveMinutes()->onOneServer();

if (! app()->isProduction()) {
    // 0 点
    Schedule::command('telescope:prune --hours=24')->daily()->onOneServer();
}

// 每月 1 号凌晨 00:00 执行
Schedule::command('db:create-partition')->monthly();
