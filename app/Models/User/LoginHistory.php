<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\User;

use App\Events\User\TodayFirstLogged;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

/**
 * 登录历史
 *
 * @property int $id 记录ID
 * @property int $user_id 用户ID
 * @property string $user_type 用户类型
 * @property string $ip 登录IP
 * @property int|null $port 端口
 * @property string $platform 操作系统平台
 * @property string $device 登录设备
 * @property string $browser 登录使用的浏览器
 * @property string $user_agent 用户代理
 * @property string $address 用户地址
 * @property Carbon|null $login_at 登录时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class LoginHistory extends Model
{
    // 时间定义
    public const CREATED_AT = 'login_at';
    public const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'user_type', 'ip', 'port', 'platform', 'device', 'browser', 'user_agent', 'address', 'login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_id', 'user_type',
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
            'user_type' => 'string',
            'ip' => 'string',
            'port' => 'integer',
            'platform' => 'string',
            'device' => 'string',
            'browser' => 'string',
            'user_agent' => 'string',
            'address' => 'string',
            'login_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required before the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            if ($model->user_agent) {
                $agent = parse_user_agent($model->user_agent);
                $model->platform = $agent['platform'] ?: null;
                $model->device = $agent['device'] ?: null;
                $model->browser = $agent['browser'] ?: null;
            }
            if ($model->ip) {
                $model->address = ip_address($model->ip);
            }
        });
        static::created(function (LoginHistory $model) {
            if ($model->user instanceof User) {
                $model->user->extra->increment('login_count', 1, [
                    'last_login_ip' => $model->ip,
                    'last_login_at' => $model->login_at,
                ]);
            } else {
                $model->user->increment('login_count', 1, [
                    'last_login_ip' => $model->ip,
                    'last_login_at' => $model->login_at,
                ]);
            }
            if (static::isTodayLogged($model->user_id, $model->user_type)) {// 当天首次登录
                Event::dispatch(new TodayFirstLogged($model));
            }
        });
    }

    /**
     * 用户
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 当天是否登录过
     */
    public static function isTodayLogged(int|string $userId, $type): bool
    {
        return static::query()->where('user_id', $userId)
            ->where('user_type', $type)
            ->whereDate('login_at', '=', Carbon::now())->exists();
    }
}
