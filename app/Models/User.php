<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use App\Models\System\LoginHistory;
use App\Models\System\Social;
use App\Models\Traits\DateTimeFormatter;
use App\Models\User\UserExtra;
use App\Observers\UserObserver;
use App\Policies\UserPolicy;
use Database\Factories\UserFactory;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * 用户模型
 *
 * @property int $id 用户ID
 * @property string|null $username 用户名
 * @property string|null $email 邮箱
 * @property string|null $phone 手机号
 * @property string|null $name 昵称
 * @property string|null $avatar 头像
 * @property UserStatus $status 状态
 * @property int|null $available_points 可用积分
 * @property int|null $available_coins 可用金币
 * @property string|null $password 密码
 * @property string|null $remember_token 记住我令牌
 * @property int|null $login_count 登录次数
 * @property string|null $last_login_ip 最后登录IP
 * @property Carbon|null $vip_expires_at VIP过期时间
 * @property Carbon|null $last_active_at 最后活动时间
 * @property Carbon|null $last_login_at 最后登录时间
 * @property Carbon|null $email_verified_at 邮箱验证时间
 * @property Carbon|null $created_at 注册时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $deleted_at 删除时间
 *
 * 只读属性
 * @property-read string|null $phone_text 手机号文本
 * @property-read string $status_label 状态文本
 *
 * 关系对象
 * @property UserExtra $extra 用户扩展信息
 * @property Collection<int,LoginHistory> $loginHistories 登录历史
 * @property Collection<int,Social> $socials 社交账号
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[Table('users')]
#[Fillable(['username', 'name', 'email', 'phone', 'avatar', 'status', 'available_points', 'available_coins', 'password'])]
#[Hidden(['password', 'remember_token'])]
#[ObservedBy([UserObserver::class])]
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable
{
    use DateTimeFormatter;

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => UserStatus::STATUS_ACTIVE,
        'available_points' => 0,
        'available_coins' => 0,
        'vip_expires_at' => null,
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
            'username' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'name' => 'string',
            'avatar' => 'string',
            'status' => UserStatus::class,
            'available_points' => 'integer',
            'available_coins' => 'integer',
            'password' => 'hashed',
            'remember_token' => 'string',
            'login_count' => 'integer',
            'last_login_ip' => 'string',
            'vip_expires_at' => 'datetime',
            'last_active_at' => 'datetime',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * 获取昵称
     */
    protected function name(): Attribute
    {
        return Attribute::make(get: function (?string $value, $attributes) {
            return $value ?: $attributes['username'];
        });
    }

    /**
     * 获取手机号
     */
    protected function phoneText(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, $attributes) => mobile_replace($attributes['phone'])
        )->shouldCache();
    }

    /**
     * 获取状态标签
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status?->label() ?? ''
        )->shouldCache();
    }

    /**
     * Get the extra relation.
     */
    public function extra(): HasOne
    {
        return $this->hasOne(UserExtra::class, 'user_id', 'id');
    }

    /**
     * Get the login histories relation.
     */
    public function loginHistories(): MorphMany
    {
        return $this->morphMany(LoginHistory::class, 'user')->latest('login_at');
    }

    /**
     * Get the social relation.
     */
    public function socials(): MorphMany
    {
        return $this->morphMany(Social::class, 'user')->latest('updated_at');
    }

    /**
     * 手机号路由通知
     *
     * @param  \Illuminate\Notifications\Notification|null  $notification
     */
    public function routeNotificationForPhone($notification): ?string
    {
        return $this->phone ?: null;
    }

    /**
     * 是否有头像
     */
    public function hasAvatar(): bool
    {
        return ! empty($this->getRawOriginal('avatar'));
    }

    /**
     * 是否有密码
     */
    public function hasPassword(): bool
    {
        return ! empty($this->password);
    }

    /**
     * 是否是VIP会员
     */
    public function isVip(): bool
    {
        return $this->vip_expires_at && $this->vip_expires_at->isFuture();
    }

    /**
     * 增加 Vip 天数
     */
    public function addVipDays(int|string $days): bool
    {
        if ($this->isVip()) {
            $this->vip_expires_at = $this->vip_expires_at->addDays($days);
        } else {
            $this->vip_expires_at = Carbon::now()->addDays((int) $days);
        }

        return $this->saveQuietly();
    }

    /**
     * 重置用户密码
     */
    public function resetPassword(string $password): void
    {
        $this->password = $password;
        $this->setRememberToken(Str::random(60));
        $this->saveQuietly();
        Event::dispatch(new PasswordReset($this));
    }

    /**
     * Mark the given user's active.
     */
    public function markActive(): bool
    {
        return $this->updateQuietly(['status' => UserStatus::STATUS_ACTIVE]);
    }

    /**
     * Mark the given user's frozen.
     */
    public function markFrozen(): bool
    {
        return $this->updateQuietly(['status' => UserStatus::STATUS_FROZEN]);
    }

    /**
     * 刷新最后活动时间
     */
    public function refreshLastActiveAt(): void
    {
        if (empty($this->last_active_at) || $this->last_active_at->lt(Carbon::now()->subMinutes(5))) {
            $this->updateQuietly(['last_active_at' => Carbon::now()]);
        }
    }

    /**
     * 重置用户手机号
     */
    public function resetPhone(int|string $phone): bool
    {
        $status = $this->forceFill(['phone' => $phone])->saveQuietly();
        Event::dispatch(new PhoneReset($this));

        return $status;
    }

    /**
     * 重置用户邮箱
     */
    public function resetEmail(string $email): bool
    {
        $status = $this->forceFill(['email' => $email])->saveQuietly();
        $this->extra->forceFill(['email_verified_at' => $this->freshTimestamp()])->saveQuietly();
        Event::dispatch(new EmailReset($this));

        return $status;
    }
}
