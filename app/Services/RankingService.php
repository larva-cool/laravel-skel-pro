<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Services;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;

/**
 * 排名服务
 * $ranking = RankingService::make('user');
 * $ranking->addScores('user1', 100);
 * $ranking->getYesterdayTop10();
 * $ranking->getCurrentMonthTop10();
 * $ranking->getOneDayRankings('20230801', 0, 9);
 */
class RankingService
{
    private string $prefix;

    private Connection $redis;

    /**
     * RankingService constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $ranking, string $redis = 'default')
    {
        if (empty(trim($ranking))) {
            throw new InvalidArgumentException('排名标识不能为空');
        }
        $this->prefix = $ranking.':';
        $this->redis = Redis::connection($redis);
    }

    /**
     * 工厂方法
     *
     * @throws InvalidArgumentException
     */
    public static function getInstance(string $ranking, string $redis = 'default'): RankingService
    {
        return new RankingService($ranking, $redis);
    }

    /**
     * 添加分数
     *
     * @param  int|string  $identity  内容标识
     * @param  int  $scores  分数
     *
     * @throws InvalidArgumentException
     */
    public function addScores(int|string $identity, int $scores = 1): float|int
    {
        // 参数验证
        if (empty(trim((string) $identity))) {
            throw new InvalidArgumentException('内容标识不能为空');
        }

        if ($scores <= 0) {
            throw new InvalidArgumentException('分数必须为正整数');
        }

        // 安全处理键名
        $safeIdentity = sanitize_key((string) $identity);
        $key = $this->prefix.date('Ymd');

        return $this->redis->zincrby($key, $scores, $safeIdentity);
    }

    /**
     * 获取昨日TOP10
     *
     * @return array<int, array{string, float|int}>
     *
     * @throws InvalidArgumentException
     */
    public function getYesterdayTop10(): array
    {
        $date = Carbon::yesterday()->format('Ymd');

        return $this->getOneDayRankings($date, 0, 9);
    }

    /**
     * 获取当前月份Top 10
     *
     * @return array<int, array{string, float|int}>
     *
     * @throws InvalidArgumentException
     */
    public function getCurrentMonthTop10(): array
    {
        $dates = static::getCurrentMonthDates();

        return $this->getMultiDaysRankings($dates, 'rank:current_month', 0, 9);
    }

    /**
     * 获取最近N天Top 10
     *
     * @return array[]
     */
    public function getDaysRankings(string|int $days, int $start = 0, int $stop = 9): array
    {
        $dates = static::getDaysDates((int) $days);

        return $this->getMultiDaysRankings($dates, 'rank:days_'.$days, $start, $stop);
    }

    /**
     * 获取本周Top 10
     *
     * @return array<int, array{string, float|int}>
     *
     * @throws InvalidArgumentException
     */
    public function getCurrentWeekTop10(): array
    {
        $dates = static::getCurrentWeekDates();

        return $this->getMultiDaysRankings($dates, 'rank:current_week', 0, 9);
    }

    /**
     * 获得指定日期的排名
     *
     * @param  string  $date  格式：YYYYMMDD
     * @param  int  $start  开始行
     * @param  int  $stop  结束行
     * @return array<int, array{string, float|int}>
     *
     * @throws InvalidArgumentException
     */
    public function getOneDayRankings(string $date, int $start, int $stop): array
    {
        // 参数验证
        if (! preg_match('/^\d{8}$/', $date)) {
            throw new InvalidArgumentException('日期格式不正确，应为YYYYMMDD');
        }

        if ($start < 0 || $stop < $start) {
            throw new InvalidArgumentException('排名范围参数无效');
        }

        $key = $this->prefix.$date;

        return $this->redis->zrevrange($key, $start, $stop, ['withscores' => true]);
    }

    /**
     * 获得多天排名
     *
     * @param  array  $dates  ['20170101','20170102']
     * @param  string  $outKey  输出Key
     * @param  int  $start  开始行
     * @param  int  $stop  结束行
     * @return array<int, array{string, float|int}>
     *
     * @throws InvalidArgumentException
     */
    public function getMultiDaysRankings(array $dates, string $outKey, int $start, int $stop): array
    {
        // 参数验证
        if (empty($dates)) {
            throw new InvalidArgumentException('日期数组不能为空');
        }

        foreach ($dates as $date) {
            if (! preg_match('/^\d{8}$/', $date)) {
                throw new InvalidArgumentException("日期格式不正确: {$date}，应为YYYYMMDD");
            }
        }

        if ($start < 0 || $stop < $start) {
            throw new InvalidArgumentException('排名范围参数无效');
        }

        // 安全处理输出键
        $safeOutKey = sanitize_key($outKey);

        // 构建键数组
        $keys = array_map(fn ($date) => $this->prefix.$date, $dates);

        // 设置权重（所有日期权重相同）
        $weights = array_fill(0, count($keys), 1);

        // 合并多个有序集合
        $this->redis->zunionstore($safeOutKey, $keys, $weights);

        // 获取排名结果
        return $this->redis->zrevrange($safeOutKey, $start, $stop, ['withscores' => true]);
    }

    /**
     * 获取本周日期
     *
     * @return array<string>
     */
    public static function getCurrentWeekDates(): array
    {
        $dates = [];
        $currentDate = Carbon::now()->startOfWeek();

        for ($i = 0; $i < 7; $i++) {
            $dates[] = $currentDate->format('Ymd');
            $currentDate->addDay();
        }

        return $dates;
    }

    /**
     * 获取指定天数内的日期
     *
     * @param  int  $days  天数
     * @return array<string>
     */
    public static function getDaysDates(int $days): array
    {
        $dates = [];
        $currentDate = Carbon::now()->subDays($days);

        for ($i = 0; $i < $days; $i++) {
            $dates[] = $currentDate->format('Ymd');
            $currentDate->addDay();
        }

        return $dates;
    }

    /**
     * 获取当前月份日期
     *
     * @return array<string>
     */
    public static function getCurrentMonthDates(): array
    {
        $dates = [];
        $currentDate = Carbon::now();
        $daysInMonth = $currentDate->daysInMonth;
        $ym = $currentDate->format('Ym');

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dates[] = $ym.sprintf('%02d', $day);
        }

        return $dates;
    }
}
