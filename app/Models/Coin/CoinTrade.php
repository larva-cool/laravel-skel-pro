<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Coin;

use App\Enum\CoinType;
use App\Models\Model;
use App\Models\Traits;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 金币交易流水表
 *
 * @property int $id 流水号
 * @property int $user_id 用户ID
 * @property int $coins 交易金币数量
 * @property string $description 交易描述
 * @property CoinType $type 交易类型
 * @property int $source_id 关联模型ID
 * @property string $source_type 关联模型类型
 * @property Carbon $created_at 添加时间
 * @property-read string $type_label 类型标签
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CoinTrade extends Model
{
    use Traits\HasUser;
    public const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coin_trades';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'coins', 'description', 'type', 'source_id', 'source_type',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_id',
    ];

    /**
     * 追加显示属性
     *
     * @var array
     */
    protected $appends = [
        'type_label',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'coins' => 'integer',
            'description' => 'string',
            'type' => CoinType::class,
            'source_id' => 'integer',
            'source_type' => 'string',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
    }

    /**
     * 类型标签
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type->label()
        )->shouldCache();
    }

    /**
     * Get the source entity that the Transaction belongs to.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 获取当前用户今日的金币数量
     */
    public static function getTodayCoins(int $userId): int
    {
        return (int) self::query()
            ->where('user_id', $userId)
            ->where('coins', '>', 0)
            ->whereDate('created_at', Carbon::today())
            ->sum('coins');
    }

    /**
     * 修复当前用户的金币数量
     * 修复当前用户的金币数量，将数据库中的金币数量更新为当前用户的金币数量
     */
    public static function fixCurrentCoins(int $userId): bool
    {
        $currentCoins = (int) self::query()->where('user_id', $userId)->sum('coins');

        return (bool) User::query()->where('id', $userId)->update(['available_coins' => $currentCoins]);
    }

    /**
     * 获取当前用户的金币数量
     */
    public static function getCurrentCoins(int $userId): int
    {
        return (int) User::query()->where('id', $userId)->sum('available_coins');
    }
}
