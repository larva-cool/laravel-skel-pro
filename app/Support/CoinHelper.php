<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

use App\Enum\CoinType;
use App\Exceptions\InsufficientCoinsException;
use App\Models\Coin\CoinTrade;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * 金币助手类 - 提供用户金币管理相关功能
 *
 * 该类封装了金币的增加、减少以及金币总额更新等核心功能
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CoinHelper
{
    /**
     * 增加金币
     *
     * @throws InsufficientCoinsException|\Throwable
     */
    public static function incr(int|string|User $user, int $coins, Model $source, CoinType $type, string $desc = ''): ?CoinTrade
    {
        if ($user instanceof User) {
            $user = $user->id;
        }
        if ($coins <= 0) {
            throw new \InvalidArgumentException('金币数量必须为正数');
        }

        return self::createTrade($user, $coins, $source->getKey(), $source->getMorphClass(), $type, $desc);
    }

    /**
     * 扣除金币
     *
     * @throws InsufficientCoinsException|\Throwable
     */
    public static function decr(int|string|User $user, int $coins, Model $source, CoinType $type, string $desc = ''): ?CoinTrade
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        // 创建金币交易记录
        return self::createTrade($user, -$coins, $source->getKey(), $source->getMorphClass(), $type, $desc);
    }

    /**
     * 获取用户当前金币余额
     */
    public static function getCurrentCoins(int|string $userId): int
    {
        return CoinTrade::getCurrentCoins((int) $userId);
    }

    /**
     * 修复当前用户可用金币余额
     */
    public static function fixCurrentCoins(int|string $userId): bool
    {
        return CoinTrade::fixCurrentCoins((int) $userId);
    }

    /**
     * 创建金币交易记录
     *
     * @throws InsufficientCoinsException|\Throwable
     */
    private static function createTrade(int|string $userId, int $coins, int|string $sourceId, string $sourceType, CoinType $type, string $desc): ?CoinTrade
    {
        $conn = CoinTrade::query()->getConnection();
        // 开始事务
        $conn->beginTransaction();
        try {
            $user = UserHelper::findById((int) $userId, true);
            if ($user->available_coins + $coins < 0) {
                throw new InsufficientCoinsException(__('user.insufficient_coins'));
            }
            $trade = CoinTrade::create([
                'user_id' => (int) $user->id,
                'coins' => $coins,
                'source_id' => (int) $sourceId,
                'source_type' => $sourceType,
                'type' => $type,
                'description' => $desc,
            ]);
            // 更新用户当前金币数量
            $user->updateQuietly(['available_coins' => $user->available_coins + $trade->coins]);
            $conn->commit();

            return $trade;
        } catch (\Exception $e) {
            $conn->rollBack();
            Log::error('创建金币交易记录失败', ['exception' => $e]);
            throw $e;
        }
    }
}
