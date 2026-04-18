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
 * 邮件验证码模型
 *
 * @property string $email 邮箱
 * @property string $code 验证码
 * @property string $ip IP地址
 * @property int $state 使用状态
 * @property int $verify_count 验证次数
 * @property Carbon $created_at 创建时间
 * @property Carbon|null $usage_at 使用时间
 * @property Carbon $send_at 发送时间
 * @property User $user 用户模型
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class MailCode extends Model
{
    use HasFactory, MassPrunable;

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
    protected $table = 'mail_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email', 'code', 'ip', 'state', 'verify_count', 'usage_at', 'send_at',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'state' => 0,
        'verify_count' => 0,
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
            'ip' => 'string',
            'state' => 'integer',
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
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * 获取可修剪模型查询构造器。
     */
    public function prunable(): Builder
    {
        return static::query()->where('send_at', '<=', now()->subDays(180));
    }

    /**
     * 验证验证码
     */
    public function validate(int|string $input, bool $caseSensitive): bool
    {
        // 检查是否已使用
        if ($this->state === self::USED_STATE) {
            return false;
        }

        $valid = $caseSensitive ? ($input === $this->code) : strcasecmp($input, $this->code) === 0;
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
        return $this->update(['state' => self::USED_STATE, 'usage_at' => Carbon::now()]);
    }

    /**
     * 获取实例
     */
    public static function build(string $email, string $ip, string $code): MailCode
    {
        return static::create(['email' => $email, 'ip' => $ip, 'code' => $code]);
    }

    /**
     * 获取指定邮箱的验证码
     */
    public static function getCode(string $email): ?MailCode
    {
        return MailCode::query()->where('email', $email)->where('state', 0)->orderBy('send_at',
            'desc')->first();
    }

    /**
     * 获取今日IP发送的总次数
     */
    public static function getIpTodayCount(string $ip): int
    {
        return static::query()
            ->where('ip', $ip)
            ->where('send_at', '>=', Carbon::today())
            ->count();
    }

    /**
     * 获取邮箱今日发送总数
     */
    public static function getMailTodayCount(string $email): int
    {
        return static::query()
            ->where('email', $email)
            ->whereDay('send_at', Carbon::today())
            ->count();
    }

    /**
     * 获取今日发送总数
     */
    public static function getTodayCount(string $email, string $ip): int
    {
        return static::getIpTodayCount($ip) + static::getMailTodayCount($email);
    }
}
