<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Enum\StatusSwitch;
use App\Models\Traits;
use App\Models\User;
use App\Models\User\LoginHistory;
use App\Support\UserHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * 管理员模型
 *
 * @property int $id 管理员ID
 * @property int $user_id 用户ID
 * @property string $username 用户名
 * @property string|null $email 邮件地址
 * @property string|null $phone 手机号
 * @property string $name 昵称
 * @property StatusSwitch $status 状态：1:active,0:frozen
 * @property string $socket_id Socket ID
 * @property string $password 密码哈希
 * @property string $remember_token 记住我 Token
 * @property string $last_login_ip 最后登录IP
 * @property int $login_count 登录次数
 * @property Carbon $last_login_at 最后登录时间
 * @property Carbon $created_at 注册时间
 * @property Carbon $updated_at 最后更新时间
 * @property Carbon|null $deleted_at 删除时间
 * @property Collection<int, AdminRole> $roles 关联的角色模型
 *
 * @property User $user 关联的用户模型
 * @property-read string $avatar 头像URL（来自关联的User模型）
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Admin extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
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
        'status' => StatusSwitch::ENABLED->value,
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
            'status' => StatusSwitch::class,
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
        static::deleting(function (Admin $model) {
            $model->roles()->detach();
        });
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
     * A user has and belongs to many roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_users', 'user_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * 检查用户是否具有指定的角色
     *
     * @param  string  $role
     * @return mixed
     */
    public function isRole(string $role): bool
    {
        return $this->roles->pluck('slug')->contains($role) || $this->roles->pluck('id')->contains($role);
    }

    /**
     * 检查当前实例是否是超管
     *
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this->isRole(config('admin.permission.administrator_id'));
    }

    /**
     * 判断是否允许查看菜单.
     *
     * @param  array|AdminMenu  $menu
     * @return bool
     */
    public function canSeeMenu($menu)
    {
        return true;
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

    protected ?\Illuminate\Support\Collection $allPermissions;

    /**
     * Get all permissions of user.
     */
    public function allPermissions(): \Illuminate\Support\Collection
    {
        if ($this->allPermissions) {
            return $this->allPermissions;
        }

        return $this->allPermissions = $this->roles->pluck('permissions')->flatten()->keyBy($this->getKeyName());
    }

    /**
     * 检查用户是否有指定的权限
     *
     * @param  string  $abilities
     * @param  array  $arguments
     * @return bool
     */
    public function can($abilities, $arguments = [])
    {
        if (!$abilities) {
            return false;
        }

        if ($this->isAdministrator()) {
            return true;
        }

        $permissions = $this->allPermissions();

        return $permissions->pluck('slug')->contains($abilities) || $permissions->pluck('id')->contains($abilities);
    }

    /**
     * Check if user has no permission.
     *
     * @param  string  $abilities
     * @return bool
     */
    public function cannot($abilities, $arguments = [])
    {
        return !$this->can($abilities);
    }
}
