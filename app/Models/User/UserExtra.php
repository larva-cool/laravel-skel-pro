<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\AsJson;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * 用户扩展信息
 *
 * @property int $user_id 用户ID
 * @property int|null $referrer_id 推荐人ID
 * @property string $last_login_ip 最后登录IP
 * @property int $invite_registered_count 邀请人数
 * @property string $invite_code 邀请码
 * @property string|null $reg_source 注册来源
 * @property int $username_change_count 用户名修改次数
 * @property int $login_count 登录次数
 * @property array|null $restore_data 恢复数据
 * @property Carbon $first_signed_at 首次签到时间
 * @property Carbon $first_active_at 首次活动时间
 * @property Carbon $last_active_at 最后活动时间
 * @property Carbon|null $last_login_at 最后登录时间
 * @property Carbon|null $phone_verified_at 手机号验证时间
 * @property Carbon|null $email_verified_at 邮箱验证时间
 *
 * 关系对象
 * @property User $user 用户实例
 * @property User|null $referrer 推荐人实例
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserExtra extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_extras';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'referrer_id', 'last_login_ip', 'invite_registered_count', 'invite_code', 'reg_source', 'username_change_count',
        'login_count', 'collection_count', 'first_signed_at', 'first_active_at', 'last_active_at', 'last_login_at',
        'restore_data', 'settings', 'phone_verified_at', 'email_verified_at',
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
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'invite_registered_count' => 0,
        'username_change_count' => 0,
        'login_count' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'referrer_id' => 'integer',
            'last_login_ip' => 'string',
            'invite_registered_count' => 'integer',
            'invite_code' => 'string',
            'reg_source' => 'string',
            'username_change_count' => 'integer',
            'login_count' => 'integer',
            'restore_data' => AsJson::class,
            'settings' => AsJson::class,
            'collection_count' => 'integer',
            'first_signed_at' => 'datetime',
            'last_active_at' => 'datetime',
            'last_login_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (UserExtra $model) {
            $model->invite_code = strtolower((string) Str::ulid());
        });
    }

    /**
     * Get the user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the referrer relation.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
