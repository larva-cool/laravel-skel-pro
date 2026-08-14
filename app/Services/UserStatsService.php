<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\User\UserStat;
use Illuminate\Support\Carbon;

/**
 * 用户统计服务
 *
 * 按日汇总前台用户的总数、新增数、活跃数，并写入 user_stats 表。
 * 支持统计任意指定日期，可用于定时任务每日执行或历史数据补录。
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserStatsService
{
    /**
     * 统计指定日期的用户数据并入库。
     *
     * 若该日期的统计记录已存在，则覆盖更新（基于 stat_date 唯一键 upsert）。
     *
     * @param  \Illuminate\Support\Carbon|string|null  $date  统计日期，默认为今天
     * @return UserStat 统计记录
     */
    public function record(Carbon|string|null $date = null): UserStat
    {
        $statDate = $this->parseDate($date);

        $stats = $this->calculate($statDate);

        UserStat::query()->upsert(
            [$stats],
            ['stat_date'],
            ['total_user_count', 'new_user_count', 'active_user_count'],
        );

        return UserStat::query()->where('stat_date', $statDate->toDateString())->firstOrFail();
    }

    /**
     * 补录一个日期区间内每天的用户统计。
     *
     * @param  \Illuminate\Support\Carbon|string  $start  起始日期
     * @param  \Illuminate\Support\Carbon|string|null  $end  结束日期，默认为今天
     * @return array<int, UserStat> 写入的记录集合
     */
    public function backfill(Carbon|string $start, Carbon|string|null $end = null): array
    {
        $startDate = $this->parseDate($start)->startOfDay();
        $endDate = $this->parseDate($end)->startOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $records = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $stats = $this->calculate($date);
            UserStat::query()->upsert(
                [$stats],
                ['stat_date'],
                ['total_user_count', 'new_user_count', 'active_user_count'],
            );
            $records[] = UserStat::query()->where('stat_date', $date->toDateString())->firstOrFail();
        }

        return $records;
    }

    /**
     * 计算指定日期的用户统计指标。
     *
     * @return array{stat_date: string, total_user_count: int, new_user_count: int, active_user_count: int}
     */
    public function calculate(Carbon $date): array
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        return [
            'stat_date' => $dayStart->toDateString(),
            // 截止当日结束的累计用户总数（含已软删除用户）
            'total_user_count' => User::query()
                ->where('created_at', '<=', $dayEnd)
                ->count(),
            // 当日新注册用户数
            'new_user_count' => User::query()
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count(),
            // 当日活跃用户数（最后活动时间落在当天）
            'active_user_count' => User::query()
                ->whereBetween('last_active_at', [$dayStart, $dayEnd])
                ->count(),
        ];
    }

    /**
     * 将日期参数统一解析为 Carbon 实例。
     */
    protected function parseDate(Carbon|string|null $date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date->copy()->startOfDay();
        }

        if (is_string($date)) {
            return Carbon::parse($date)->startOfDay();
        }

        return Carbon::today();
    }
}
