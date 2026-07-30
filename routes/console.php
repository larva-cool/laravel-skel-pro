<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('auth:clear-resets')->dailyAt('1:00')->onOneServer();
Schedule::command('model:prune')->dailyAt('1:05')->onOneServer();
