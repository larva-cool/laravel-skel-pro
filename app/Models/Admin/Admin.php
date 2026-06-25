<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Enum\UserStatus;
use App\Models\Traits;
use App\Models\User;
use App\Models\User\LoginHistory;
use App\Support\UserHelper;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * 管理员模型
 *
 * @property int $id 管理员ID
 * @property int $user_id 用户ID
 * @property string $username 用户名
 * @property string|null $email 邮件地址
 * @property string|null $phone 手机号
 * @property string $name 昵称
 * @property UserStatus $status 状态
 * @property string $socket_id Socket ID
 * @property string $password 密码哈希
 * @property string $remember_token 记住我 Token
 * @property string $last_login_ip 最后登录IP
 * @property int $login_count 登录次数
 * @property Carbon $last_login_at 最后登录时间
 * @property Carbon $created_at 注册时间
 * @property Carbon $updated_at 最后更新时间
 * @property Carbon|null $deleted_at 删除时间
 * @property User $user 关联的用户模型
 * @property-read string $avatar 头像URL（来自关联的User模型）
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Admin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;
    use Traits\DateTimeFormatter;
    use Traits\HasApiTokens;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'username', 'email', 'phone', 'name', 'status', 'socket_id', 'password', 'last_login_ip',
        'login_count', 'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => UserStatus::STATUS_ACTIVE->value,
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'user',
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
            'username' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'name' => 'string',
            'status' => UserStatus::class,
            'socket_id' => 'string',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (Admin $model) {
            $user = UserHelper::findOrCreatePhone($model->phone);
            $model->user_id = $user?->id;
        });
        static::deleting(function (Admin $model) {});
    }

    /**
     * 获取头像
     */
    protected function avatar(): Attribute
    {
        $this->loadMissing('user');

        return Attribute::make(
            get: function ($value, $attributes) {
                return $this->user?->avatar.'?time='.time();
            },
            set: function ($value, $attributes) {
                $this->user?->updateQuietly(['avatar' => $value]);
            }
        );
    }

    /**
     * Admin has and belongs to many user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the login histories relation.
     */
    public function loginHistories(): MorphMany
    {
        return $this->morphMany(LoginHistory::class, 'user')
            ->latest('login_at');
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
     * 重置用户密码
     */
    public function resetPassword(string $password): void
    {
        $this->password = $password;
        $this->setRememberToken(Str::random(60));
        $this->saveQuietly();
        Event::dispatch(new PasswordReset($this));
    }
}
