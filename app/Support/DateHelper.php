<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * 日期时间助手
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class DateHelper
{
    /**
     * 获取最近XX天的范围
     */
    public static function getRecentDaysRange(int|string $days, string $format = 'Y-m-d H:i:s'): array
    {
        // 获取当前时间作为结束时间
        $endTime = Carbon::now()->endOfDay();
        // 计算开始时间
        $startTime = $endTime->copy()->subDays((int) $days - 1)->startOfDay();

        return [
            'start' => $startTime->format($format),
            'end' => $endTime->format($format),
        ];
    }

    /**
     * 获取本月开始和结束
     */
    public static function getThisMonthRange(string $format = 'Y-m-d'): array
    {
        // 获取当前时间
        $now = Carbon::now();

        // 获取本月开始时间
        $thisMonthStart = $now->copy()->startOfMonth();

        // 获取本月结束时间
        $thisMonthEnd = $now->copy()->endOfMonth();

        return [
            'start' => $thisMonthStart->format($format),
            'end' => $thisMonthEnd->format($format),
        ];
    }

    /**
     * 获取上月开始和结束
     *
     * @param  string  $format  返回的时间格式
     */
    public static function getLastMonthRange(string $format = 'Y-m-d'): array
    {
        $now = Carbon::now();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        return [
            'start' => $lastMonthStart->format($format),
            'end' => $lastMonthEnd->format($format),
        ];
    }
}
