<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Announcement;

use App\Casts\AsJson;
use App\Casts\StorageUrl;
use App\Enum\StatusSwitch;
use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * 公告管理
 *
 * @property int $id ID
 * @property array $coverage 覆盖范围
 * @property string $title 标题
 * @property string $content 内容
 * @property string $image 图片
 * @property string $jump_url 跳转URL
 * @property StatusSwitch $status 状态
 * @property int $effective_time_type 生效时间类型,0:立即生效,1:定时生效
 * @property Carbon $effective_start_time 生效开始时间
 * @property Carbon $effective_end_time 生效结束时间
 * @property int $read_count 已读次数
 * @property int $admin_id 管理员ID
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property Collection<int, AnnouncementRead> $reads 已读关系
 *
 * @method Builder active(string $coverage) 查询已发布的公告
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Announcement extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'announcements';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'coverage', 'title', 'content', 'image', 'jump_url', 'status', 'admin_id', 'read_count', 'effective_time_type', 'effective_start_time', 'effective_end_time',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'read_count' => 0,
        'effective_time_type' => 0,
        'status' => StatusSwitch::ENABLED->value,
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
            'coverage' => AsJson::class,
            'title' => 'string',
            'content' => 'string',
            'image' => StorageUrl::class,
            'jump_url' => 'string',
            'status' => StatusSwitch::class,
            'admin_id' => 'integer',
            'effective_time_type' => 'integer',
            'effective_start_time' => 'datetime',
            'effective_end_time' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
     * 已读关系
     */
    public function read(): HasOne
    {
        return $this->hasOne(AnnouncementRead::class);
    }

    /**
     * 通知已读关系
     */
    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /**
     * 查询已发布的协议
     */
    protected function scopeActive(Builder $query, string $coverage): Builder
    {
        return $query->where('status', '=', StatusSwitch::ENABLED->value)
            ->where(function (Builder $query) {
                $query->where('effective_time_type', '=', 0)
                    ->orWhere(function (Builder $query) {
                        $query->where('effective_time_type', '=', 1)
                            ->where('effective_start_time', '<=', now())
                            ->where('effective_end_time', '>=', now());
                    });
            })
            ->whereJsonContains('coverage', $coverage);
    }

    public function isRead(int $userId, string $userType): bool
    {
        $existing = $this->reads()->where('user_id', $userId)->where('user_type', $userType)->first();
        if ($existing) {
            return true;
        }

        return false;
    }

    /**
     * 标记公告为已读
     *
     * @param  int  $userId  用户ID
     */
    public function markAsRead(int $userId, string $userType): bool
    {
        if ($this->isRead($userId, $userType)) {
            return true;
        }

        return (bool) $this->reads()->create(['user_id' => $userId, 'user_type' => $userType]);
    }

    /**
     * 获取未读公告数量
     */
    public static function getUnreadCount(int $userId, string $userType): int
    {
        return self::query()
            ->active($userType)
            ->whereDoesntHave('reads', function ($query) use ($userId, $userType) {
                $query->where('user_id', $userId)->where('user_type', $userType);
            })
            ->count();
    }

    /**
     * 获取最后一条公告
     */
    public static function getLastNotice(string $userType): ?Announcement
    {
        return self::query()
            ->active($userType)
            ->whereJsonContains('coverage', $userType)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * 获取未读最后一条公告
     */
    public static function getUnreadLastNotice(int $userId, string $userType): ?Announcement
    {
        $item = self::query()
            ->active($userType)
            ->whereJsonContains('coverage', $userType)
            ->whereDoesntHave('read', function ($query) use ($userId, $userType) {
                $query->where('user_id', $userId)->where('user_type', $userType);
            })
            ->orderByDesc('id')
            ->first();
        if (! $item) {
            return self::getLastNotice($userType);
        }

        return $item;
    }
}
