<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 手机验证码
 *
 * @property int $id 验证码ID
 * @property string $scene 验证场景
 * @property string $phone 手机号
 * @property string $code 验证码
 * @property string $ip IP地址
 * @property int $state 使用状态
 * @property int $verify_count 验证次数
 * @property Carbon|null $usage_at 使用时间
 * @property Carbon $send_at 发送时间
 * @property array $result 发送结果
 * @property User|null $user
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class PhoneCode extends Model
{
    use HasFactory;
    use MassPrunable;

    // 使用状态
    public const USED_STATE = 1;

    // 时间定义
    public const CREATED_AT = 'send_at';
    public const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'phone_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'scene', 'phone', 'code', 'ip', 'state', 'verify_count', 'usage_at', 'send_at', 'result',
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
            'scene' => 'string',
            'ip' => 'string',
            'state' => 'integer',
            'code' => 'string',
            'verify_count' => 'integer',
            'send_at' => 'datetime',
            'usage_at' => 'datetime',
        ];
    }

    /**
     * Get the user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'phone', 'phone');
    }

    /**
     * 获取可修剪模型查询构造器。
     */
    public function prunable(): Builder
    {
        return static::query()->where('send_at', '<=', now()->subDays(180));
    }

    /**
     * 记录验证码
     *
     * @param  string  $scene  验证码场景
     */
    public static function build(int|string $phone, string $ip, string $code, string $scene = 'default'): PhoneCode
    {
        return static::create(['phone' => $phone, 'ip' => $ip, 'code' => $code, 'scene' => $scene]);
    }

    /**
     * 获取指定手机号的验证码
     */
    public static function getCode(int|string $phone): ?PhoneCode
    {
        return PhoneCode::query()->where('phone', $phone)->where('state', 0)->orderByDesc('send_at')->first();
    }

    /**
     * 验证验证码
     */
    public function validate(int|string $input, bool $caseSensitive): bool
    {
        $valid = $caseSensitive ? ((string) $input === $this->code) : strcasecmp((string) $input, $this->code) === 0;
        if (! $valid) {
            $this->increment('verify_count');
        } else {
            $this->makeUsed();
        }

        return $valid;
    }

    /**
     * 标记为已使用
     */
    public function makeUsed(): bool
    {
        return (bool) $this->update(['state' => static::USED_STATE, 'usage_at' => Carbon::now()]);
    }

    /**
     * 获取IP今日发送总数
     */
    public static function getIpTodayCount(string $ip): int
    {
        return static::query()
            ->where('ip', $ip)
            ->whereDay('send_at', Carbon::today())
            ->whereNotNull('usage_at')
            ->count();
    }

    /**
     * 获取今日发送总数
     */
    public static function getPhoneTodayCount(int|string $phone): int
    {
        return static::query()
            ->where('phone', $phone)
            ->whereDay('send_at', Carbon::today())
            ->whereNotNull('usage_at')
            ->count();
    }

    /**
     * 获取今日发送总数
     */
    public static function getTodayCount(int|string $phone, string $ip): int
    {
        return static::getIpTodayCount($ip) + static::getPhoneTodayCount($phone);
    }

    /**
     * 获取IP当前小时发送总数
     */
    public static function getIpHourCount(string $ip): int
    {
        return static::query()
            ->where('ip', $ip)
            ->whereBetween('send_at', [Carbon::now()->startOfHour(), Carbon::now()->endOfHour()])
            ->whereNotNull('usage_at')
            ->count();
    }

    /**
     * 获取当前小时发送总数
     */
    public static function getPhoneHourCount(int|string $phone): int
    {
        return static::query()
            ->where('phone', $phone)
            ->whereBetween('send_at', [Carbon::now()->startOfHour(), Carbon::now()->endOfHour()])
            ->whereNotNull('usage_at')
            ->count();
    }

    /**
     * 获取当前小时发送总数
     */
    public static function getHourCount(int|string $phone, string $ip): int
    {
        return static::getIpHourCount($ip) + static::getPhoneHourCount($phone);
    }
}
