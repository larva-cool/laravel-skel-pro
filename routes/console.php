<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// 每天凌晨 1:00 清理过期的密码重置令牌
Schedule::command('auth:clear-resets')->dailyAt('1:00')->onOneServer();

// 每天凌晨 1:05 清理过期的模型实例
Schedule::command('model:prune')->dailyAt('1:05')->onOneServer();

// 每天凌晨 1:10 统计昨天的用户数据（总数、新增、活跃）
Schedule::command('stats:user')->dailyAt('1:10')->onOneServer();

if (! app()->isProduction()) {
    // 每天凌晨 1:20 清理 Telescope 日志
    Schedule::command('telescope:prune --hours=48')->dailyAt('1:20')->onOneServer();
}
