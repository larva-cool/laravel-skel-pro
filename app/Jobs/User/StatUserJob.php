<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Jobs\User;

use App\Models\Coin\CoinTrade;
use App\Models\Point\PointTrade;
use App\Models\User;
use App\Models\User\UserExtra;
use App\Models\User\UserStat;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * 用户统计任务
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StatUserJob implements ShouldQueue
{
    use Queueable;

    protected string $statDate;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $statDate = null)
    {
        if (is_null($statDate)) {
            $statDate = Carbon::yesterday()->toDateString();
        }
        $this->statDate = $statDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! UserStat::query()->where('stat_date', $this->statDate)->exists()) {
            $totalCount = User::query()->count();
            $totalCoin = User::query()->sum('available_coins');
            $totalPoint = User::query()->sum('available_points');
            $registerCount = User::query()
                ->where('created_at', '>=', $this->statDate.' 00:00:01')
                ->where('created_at', '<=', $this->statDate.' 23:59:59')
                ->count('id');
            $activeUserCount = UserExtra::query()
                ->where('last_active_at', '>=', $this->statDate.' 00:00:01')
                ->where('last_active_at', '<=', $this->statDate.' 23:59:59')
                ->count();
            $coinTradeCount = CoinTrade::query()
                ->where('created_at', '>=', $this->statDate.' 00:00:01')
                ->where('created_at', '<=', $this->statDate.' 23:59:59')
                ->count();
            $pointTradeCount = PointTrade::query()
                ->where('created_at', '>=', $this->statDate.' 00:00:01')
                ->where('created_at', '<=', $this->statDate.' 23:59:59')
                ->count();
            UserStat::create([
                'stat_date' => $this->statDate,
                'total_user_count' => $totalCount,
                'total_coin_count' => $totalCoin,
                'total_point_count' => $totalPoint,
                'new_user_count' => $registerCount,
                'active_user_count' => $activeUserCount,
                'coin_transaction_count' => $coinTradeCount,
                'point_transaction_count' => $pointTradeCount,
            ]);
        }
    }
}
