<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Agreement;

use App\Enum\StatusSwitch;
use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * 协议管理
 *
 * @property int $id ID
 * @property string $type 类型
 * @property string $title 标题
 * @property StatusSwitch $status 状态
 * @property string $content 内容
 * @property int $order 排序
 * @property int $admin_id 发布者
 * @property Carbon $created_at 添加时间
 * @property Carbon $updated_at 更新时间
 * @property Carbon $deleted_at 删除时间
 * @property AgreementRead $agree 同意记录
 * @property Collection<int, AgreementRead> $reads 已读关系
 *
 * @method Builder active(string $type) 查询已发布的协议
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class Agreement extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agreements';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type', 'title', 'content', 'status', 'order', 'admin_id',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => StatusSwitch::ENABLED->value,
        'order' => 0,
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
            'type' => 'string',
            'title' => 'string',
            'content' => 'string',
            'status' => StatusSwitch::class,
            'admin_id' => 'integer',
            'order' => 'integer',
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
     * 查询已发布的协议
     */
    protected function scopeActive(Builder $query, string $type): Builder
    {
        return $query->where('status', '=', StatusSwitch::ENABLED->value)->where('type', '=', $type);
    }

    /**
     * 协议同意关系定义
     */
    public function agree(): HasOne
    {
        return $this->hasOne(AgreementRead::class);
    }

    /**
     * 协议已读关系
     */
    public function reads(): HasMany
    {
        return $this->hasMany(AgreementRead::class);
    }

    /**
     * 是否已经同意
     */
    public function isAgree(int|string $userId): bool
    {
        $existing = $this->agree()->where('user_id', $userId)->first();
        if ($existing) {
            return true;
        }

        return false;
    }

    /**
     * 标记协议为已读
     *
     * @param  int  $userId  用户ID
     */
    public function markAsAgree(int $userId): bool
    {
        if ($this->isAgree($userId)) {
            return true;
        }

        return (bool) $this->agree()->create(['user_id' => $userId]);
    }

    /**
     * 获取未同意协议数量
     */
    public static function getNotAgreedCount(int $userId, string $type): int
    {
        return self::active($type)
            ->whereDoesntHave('agree', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();
    }
}
