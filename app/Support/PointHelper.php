<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

use App\Enum\PointType;
use App\Exceptions\InsufficientPointsException;
use App\Models\Point\PointRecord;
use App\Models\Point\PointTrade;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 积分助手类 - 提供用户积分管理相关功能
 *
 * 该类封装了积分的增加、减少、过期处理以及积分总额更新等核心功能，
 * 遵循先过期先使用的原则处理积分消耗，并提供异常处理机制。
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class PointHelper
{
    /**
     * 增加用户积分
     *
     * 创建积分交易记录并更新用户积分总额
     *
     * @param  int|string  $userId  用户ID
     * @param  int  $points  增加的积分数量
     * @param  Model  $source  积分来源模型
     * @param  PointType  $type  交易类型，使用本类的TYPE_*常量
     * @param  string  $desc  交易描述
     * @return PointTrade 创建的积分交易记录模型实例
     */
    public static function incr(int|string $userId, int $points, Model $source, PointType $type, string $desc = ''): PointTrade
    {
        // 计算积分过期时间（默认365天后）
        $expireTime = Carbon::now()->addDays((int) settings('user.point_expiration', 365));

        // 创建积分交易记录
        return self::createTradeLog($userId, $points, $source->getKey(), $source->getMorphClass(), $type, $desc, $expireTime);
    }

    /**
     * 减少用户积分
     *
     * 根据先过期先使用的原则消耗用户积分，积分不足时抛出异常
     *
     * @param  int|string  $userId  用户ID
     * @param  int  $point  要减少的积分数量
     * @param  Model  $source  积分消耗来源模型
     * @param  PointType  $type  交易类型，使用本类的TYPE_*常量
     * @param  string  $desc  交易描述
     * @return bool 操作是否成功
     *
     * @throws InsufficientPointsException 当积分不足时抛出
     */
    public static function decr(int|string $userId, int $point, Model $source, PointType $type, string $desc): bool
    {
        $sumPoint = 0;            // 累计可用积分
        $targetRecord = null;     // 目标积分记录
        $recordIds = [];          // 要删除的积分记录ID数组
        $foundEnough = false;     // 是否找到足够的积分标志

        // 使用chunkById高效查询并处理积分记录（避免大量数据时的内存问题）
        PointRecord::query()
            ->where('user_id', $userId)
            ->where('expired_at', '>', Carbon::now()) // 只查询未过期的积分
            ->orderBy('expired_at', 'ASC')            // 按过期时间升序（先过期先使用）
            ->orderBy('id', 'ASC')                    // 按ID升序（相同过期时间时按创建顺序）
            ->chunkById(10, function ($pointRecords) use (&$sumPoint, $point, &$recordIds, &$targetRecord, &$foundEnough) {
                if ($foundEnough) {
                    return; // 已找到足够积分，提前退出
                }

                /** @var PointRecord $pointRecord */
                foreach ($pointRecords as $pointRecord) {
                    $sumPoint += $pointRecord->points;
                    $recordIds[] = $pointRecord->id;

                    if ($sumPoint >= $point) {
                        $targetRecord = $pointRecord;
                        $foundEnough = true;
                        break;
                    }
                }
            });

        // 检查积分是否足够
        if (! $targetRecord || $sumPoint < $point) {
            throw new InsufficientPointsException('积分不足，当前可用积分: '.$sumPoint);
        } elseif ($sumPoint == $point) {
            // 积分刚好足够，添加最后一条记录ID
            $recordIds[] = $targetRecord->id;
        } else {
            // 积分有剩余，需要拆分最后一条记录
            $leftPoint = $sumPoint - $point;
            $usePoint = $targetRecord->points - $leftPoint;

            // 创建新的积分记录存储剩余积分
            $newPointRecord = $targetRecord->replicate()->fill([
                'points' => $leftPoint,
                'updated_at' => Carbon::now(),
            ]);
            $newPointRecord->save();

            // 更新最后一条记录的积分值
            PointRecord::query()->where('id', $targetRecord->id)->update(['points' => $usePoint]);
        }

        // 删除已使用的积分记录
        PointRecord::query()->whereIn('id', $recordIds)->delete();

        // 创建积分交易记录（负值表示减少）
        self::createTradeLog($userId, -$point, $source->getKey(), $source->getMorphClass(), $type, $desc);

        return true;
    }

    /**
     * 处理过期积分
     *
     * 回收用户所有已过期的积分
     *
     * @param  int|string  $userId  用户ID
     */
    public static function handlingExpired(int|string $userId): void
    {
        // 查询所有已过期的积分记录
        $expiredRecords = PointRecord::query()
            ->where('user_id', $userId)
            ->where('expired_at', '<', Carbon::now()) // 只查询已过期的积分
            ->get();

        if ($expiredRecords->isEmpty()) {
            return; // 没有过期积分需要处理
        }
        // 计算过期积分总数
        $totalExpiredPoints = $expiredRecords->sum('points');

        // 创建积分交易记录（负值表示减少）
        self::createTradeLog($userId, -$totalExpiredPoints, 0, PointRecord::class, PointType::TYPE_RECOVERY, '过期回收');

        // 删除已过期的积分记录
        PointRecord::query()
            ->where('user_id', $userId)
            ->where('expired_at', '<', Carbon::now())
            ->delete();
    }

    /**
     * 更新用户可用积分总额
     *
     * 重新计算用户当前可用积分并更新到用户表
     *
     * @param  int|string  $userId  用户ID
     */
    public static function updatePointTotal(int|string $userId): void
    {
        // 计算用户当前可用积分总额（未过期的积分）
        $pointTotal = PointRecord::query()
            ->where('user_id', $userId)
            ->where('expired_at', '>', Carbon::now())
            ->sum('points');

        // 更新用户表中的可用积分字段
        User::query()->where('id', $userId)->update(['available_points' => $pointTotal]);
    }

    /**
     * 创建积分交易记录
     *
     * @param  int|string  $userId  用户ID
     * @param  int  $points  交易积分数量（正值表示增加，负值表示减少）
     * @param  int|string  $sourceId  关联模型ID
     * @param  string  $sourceType  关联模型类型
     * @param  PointType  $type  交易类型
     * @param  string  $desc  交易描述
     * @param  ?Carbon  $expiredAt  过期时间（可选）
     */
    protected static function createTradeLog(int|string $userId, int $points, int|string $sourceId, string $sourceType, PointType $type, string $desc, ?Carbon $expiredAt = null): PointTrade
    {
        $item = PointTrade::create([
            'user_id' => $userId,
            'points' => $points,
            'source_id' => $sourceId,
            'source_type' => $sourceType,
            'type' => $type,
            'description' => $desc,
            'expired_at' => $expiredAt,
        ]);

        // 更新用户可用积分总额
        static::updatePointTotal($userId);

        return $item;
    }
}
