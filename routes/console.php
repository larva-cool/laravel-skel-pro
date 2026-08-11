<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('auth:clear-resets')->dailyAt('1:00')->onOneServer();
Schedule::command('model:prune')->dailyAt('1:05')->onOneServer();
// 每天凌晨 1:10 统计昨天的用户数据（总数、新增、活跃）
Schedule::command('stats:user')->dailyAt('1:10')->onOneServer();
