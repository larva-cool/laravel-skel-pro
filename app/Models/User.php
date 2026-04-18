<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\SocialProvider;
use App\Enum\UserStatus;
use App\Events\User\EmailReset;
use App\Events\User\EmailVerified;
use App\Events\User\PayPasswordReset;
use App\Events\User\PhoneReset;
use App\Events\User\PhoneVerified;
use App\Events\User\UsernameReset;
use App\Models\Coin\CoinTrade;
use App\Models\Point\PointTrade;
use App\Models\User\Address;
use App\Models\User\LoginHistory;
use App\Models\User\UserExtra;
use App\Models\User\UserGroup;
use App\Models\User\UserProfile;
use App\Models\User\UserSocial;
use App\Observers\UserObserver;
use App\Support\UserHelper;
use Database\Factories\UserFactory;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 用户模型
 *
 * @property int $id 用户ID
 * @property int $group_id 用户组ID
 * @property string $username 用户名
 * @property string|null $email 邮件地址
 * @property string|null $phone 手机号
 * @property string $name 昵称
 * @property string $avatar 头像访问 Url
 * @property UserStatus $status 状态
 * @property int $available_points 可用积分
 * @property int $available_coins 可用金币
 * @property string $socket_id Socket ID
 * @property string $device_id 设备ID
 * @property string $password 密码哈希
 * @property string $pay_password 支付密码哈希
 * @property string $remember_token 记住我 Token
 * @property Carbon|null $vip_expires_at VIP过期时间
 * @property Carbon $created_at 注册时间
 * @property Carbon $updated_at 最后更新时间
 * @property Carbon|null $deleted_at 删除时间
 *
 * 只读属性
 * @property-read string $socket_status Socket 状态
 * @property-read string|null $phone_text 手机号文本
 * @property-read string $status_label 状态文本
 * @property-read string $invite_link 邀请链接
 *
 * 关系对象
 * @property UserGroup $group 用户组
 * @property UserProfile $profile 个人信息
 * @property UserExtra $extra 用户扩展信息
 * @property Collection<int,UserSocial> $socials 用户社交账号
 * @property UserSocial|null $wechatMp 微信公众号
 * @property UserSocial|null $wechatApp 微信应用
 * @property UserSocial|null $wechatMiniProgram 微信小程序
 * @property Collection<int,PointTrade> $points 积分交易明细
 * @property Collection<int,CoinTrade> $coins 金币交易明细
 * @property Collection<int,LoginHistory> $loginHistories 登录历史
 * @property Collection<int,User> $invites 邀请用户
 *
 * @method Builder active() 查询活动用户
 * @method Builder keyword(string $keyword) 根据关键词搜索
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    use Traits\DateTimeFormatter;
    use Traits\HasApiTokens;

    // 默认头像
    public const DEFAULT_AVATAR = 'img/avatar.png';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group_id', 'username', 'email', 'phone', 'name', 'avatar', 'status', 'available_points', 'available_coins',
        'socket_id', 'device_id', 'password', 'vip_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'pay_password', 'remember_token',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => UserStatus::STATUS_ACTIVE->value,
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
            'group_id' => 'integer',
            'username' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'name' => 'string',
            'avatar' => 'string',
            'status' => UserStatus::class,
            'available_points' => 'integer',
            'available_coins' => 'integer',
            'device_id' => 'string',
            'socket_id' => 'string',
            'password' => 'hashed',
            'pay_password' => 'hashed',
            'vip_expires_at' => 'datetime',
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
     * 获取头像
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => UserHelper::getAvatar($value)
        );
    }

    /**
     * 在线状态
     */
    protected function socketStatus(): Attribute
    {
        return Attribute::make(get: function ($value, $attributes) {
            return ! empty($attributes['socket_id']) ? 'online' : 'offline';
        });
    }

    /**
     * 获取邀请链接
     */
    protected function inviteLink(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, $attributes) {
                return settings('system.m_url').'/#/register?invite_code=user@'.$attributes['id'];
            },
        )->shouldCache();
    }

    /**
     * 获取状态标签
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->label()
        )->shouldCache();
    }

    /**
     * Get the group relation.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Get the profile relation.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    /**
     * Get the extra relation.
     */
    public function extra(): HasOne
    {
        return $this->hasOne(UserExtra::class, 'user_id', 'id');
    }

    /**
     * Get the socials' relation.
     */
    public function socials(): HasMany
    {
        return $this->hasMany(UserSocial::class)->orderBy('id');
    }

    /**
     * Get the wechat mp relation.
     */
    public function wechatMp(): HasOne
    {
        return $this->socials()->one()->where('provider', SocialProvider::WECHAT_MP->value);
    }

    /**
     * Get the wechat app relation.
     */
    public function wechatApp(): HasOne
    {
        return $this->socials()->one()->where('provider', SocialProvider::WECHAT_APP->value);
    }

    /**
     * Get the wechat mini program relation.
     */
    public function wechatMiniProgram(): HasOne
    {
        return $this->socials()->one()->where('provider', SocialProvider::WECHAT_MINI_PROGRAM->value);
    }

    /**
     * Get the point trades relation.
     */
    public function points(): HasMany
    {
        return $this->hasMany(PointTrade::class)->latest('id');
    }

    /**
     * Get the coin trades relation.
     */
    public function coins(): HasMany
    {
        return $this->hasMany(CoinTrade::class)->latest('id');
    }

    /**
     * Get the address relation.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->orderBy('id');
    }

    /**
     * Get the default address relation.
     */
    public function defaultAddress(): HasOne
    {
        return $this->addresses()->one()->where('is_default', true)->latestOfMany();
    }

    /**
     * Get the login histories relation.
     */
    public function loginHistories(): MorphMany
    {
        return $this->morphMany(LoginHistory::class, 'user')->latest('login_at');
    }

    /**
     * Get the invite's relation.
     */
    public function invites(): HasManyThrough|User
    {
        return $this->hasManyThrough(User::class, UserExtra::class, 'referrer_id', 'id', 'id', 'user_id');
    }

    /**
     * 查询活的用户
     */
    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '=', UserStatus::STATUS_ACTIVE->value);
    }

    /**
     * 根据关键词搜索
     */
    protected function scopeKeyword(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $query) use ($keyword) {
            $query->where('username', 'like', '%'.$keyword.'%')
                ->orWhere('name', 'like', '%'.$keyword.'%')
                ->orWhere('email', 'like', '%'.$keyword.'%')
                ->orWhere('phone', 'like', '%'.$keyword.'%');
        });
    }

    /**
     * 获取手机号
     *
     * @param  Notification|null  $notification
     */
    public function routeNotificationForPhone($notification): ?string
    {
        return $this->phone ?: null;
    }

    /**
     * 获取微信 Openid
     *
     * @param  Notification|null  $notification
     */
    public function routeNotificationForWechat($notification): ?string
    {
        $this->loadMissing('wechatMp');

        return ! empty($this->wechatMp->openid) ? $this->wechatMp->openid : null;
    }

    /**
     * 获取微信小程序 Openid
     *
     * @param  Notification|null  $notification
     */
    public function routeNotificationForWechatMini($notification): ?string
    {
        $this->loadMissing('wechatMiniProgram');

        return ! empty($this->wechatMiniProgram->openid) ? $this->wechatMiniProgram->openid : null;
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
     * Determine if the user has verified their phone number.
     */
    public function hasVerifiedPhone(): bool
    {
        $this->loadMissing('extra');

        return ! is_null($this->extra->phone_verified_at);
    }

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        $this->loadMissing('extra');

        return ! is_null($this->extra->email_verified_at);
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
     * Mark the given user's phone as verified.
     */
    public function markPhoneAsVerified(): bool
    {
        $this->loadMissing('extra');
        $status = $this->extra->forceFill(['phone_verified_at' => $this->freshTimestamp()])->saveQuietly();
        Event::dispatch(new PhoneVerified($this));

        return $status;
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        $this->loadMissing('extra');
        $status = $this->extra->forceFill(['email_verified_at' => $this->freshTimestamp()])->saveQuietly();
        Event::dispatch(new EmailVerified($this));

        return $status;
    }

    /**
     * Mark the given user's active.
     */
    public function markActive(): bool
    {
        return $this->updateQuietly(['status' => UserStatus::STATUS_ACTIVE->value]);
    }

    /**
     * Mark the given user's frozen.
     */
    public function markFrozen(): bool
    {
        return $this->updateQuietly(['status' => UserStatus::STATUS_FROZEN->value]);
    }

    /**
     * Determine if the user has active.
     */
    public function isFrozen(): bool
    {
        return $this->status->isFrozen();
    }

    /**
     * 验证支付密码是否正确
     */
    public function verifyPayPassword($password): bool
    {
        return $this->pay_password && Hash::check($password, $this->pay_password);
    }

    /**
     * 是否是VIP会员
     */
    public function isVip(): bool
    {
        return $this->vip_expires_at && $this->vip_expires_at->isFuture();
    }

    /**
     * 刷新最后活动时间
     */
    public function refreshLastActiveAt(): void
    {
        $this->loadMissing('extra');

        if (empty($this->extra->last_active_at) || $this->extra->last_active_at->lt(Carbon::now()->subMinutes(5))) {
            $this->extra->updateQuietly(['last_active_at' => Carbon::now()]);
        }
        // 首次活动时间
        $this->refreshFirstActiveAt();
    }

    /**
     * 刷新首次活动时间
     *
     * @return $this
     */
    public function refreshFirstActiveAt(): static
    {
        $this->loadMissing('extra');

        if (! $this->extra->first_active_at) {
            $this->extra->updateQuietly(['first_active_at' => Carbon::now()]);
        }

        return $this;
    }

    /**
     * 重置用户头像
     */
    public function resetAvatar(): bool
    {
        if ($this->hasAvatar() && $this->getRawOriginal('avatar') != User::DEFAULT_AVATAR) {
            try {
                if (Storage::delete($this->getRawOriginal('avatar'))) {
                    $this->forceFill(['avatar' => null])->updateQuietly();

                    return true;
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete user avatar for user ID: '.$this->id.'. Error: '.$e->getMessage());
            }

            return false;
        }

        return true;
    }

    /**
     * 重置用户名
     */
    public function resetUsername(string $username): void
    {
        $this->loadMissing('extra');

        if ($username != $this->username) {
            $this->update(['username' => $username]);
            $this->extra->increment('username_change_count');
            Event::dispatch(new UsernameReset($this));
        }
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
     * 重置用户支付密码
     */
    public function modifyPayPassword(string $password): void
    {
        $this->pay_password = $password;
        $this->saveQuietly();
        Event::dispatch(new PayPasswordReset($this));
    }

    /**
     * 重置用户手机号
     */
    public function resetPhone(int|string $phone): bool
    {
        $status = $this->forceFill(['phone' => $phone])->saveQuietly();
        $this->extra->forceFill(['phone_verified_at' => $this->freshTimestamp()])->saveQuietly();
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
