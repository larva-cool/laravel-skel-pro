<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Task;

use App\Casts\AsTaskCondition;
use App\Enums\StatusSwitch;
use App\Enums\TaskType;
use App\Models\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 任务模型
 *
 * @property int $id ID
 * @property int $group_id 任务组ID
 * @property string $name 名称
 * @property TaskType $type 类型
 * @property int $coins 奖励金币
 * @property bool $activity_bonus 活跃度加成
 * @property string $description 任务简介
 * @property array $condition 条件
 * @property int $status 任务状态
 * @property int $order 排序
 * @property int $log_count 日志数
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property Carbon $deleted_at 删除时间
 * @property TaskGroup $group 任务组
 * @property TaskLog[] $logs 任务日志
 * @property TaskLog $todayLog 今日任务完成日志
 * @property-read string $type_name
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Task extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group_id', 'name', 'type', 'coins', 'activity_bonus', 'description', 'condition', 'status', 'order', 'log_count',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'coins' => 0,
        'activity_bonus' => StatusSwitch::DISABLED,
        'order' => 0,
        'log_count' => 0,
        'status' => StatusSwitch::ENABLED,
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
            'name' => 'string',
            'type' => TaskType::class,
            'coins' => 'integer',
            'activity_bonus' => StatusSwitch::class,
            'description' => 'string',
            'condition' => AsTaskCondition::class,
            'status' => StatusSwitch::class,
            'order' => 'integer',
            'log_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * 查询活动的任务组
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', '=', StatusSwitch::ENABLED->value);
    }

    /**
     * 获取任务类型名称
     */
    public function getTypeNameAttribute(): string
    {
        return $this->type->label();
    }

    /**
     * 任务组
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TaskGroup::class, 'group_id');
    }

    /**
     * 任务日志
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class, 'task_id');
    }

    /**
     * 今日任务完成日志
     */
    public function todayLog(): HasOne
    {
        return $this->hasOne(TaskLog::class, 'task_id')
            ->where('created_at', '>=', Carbon::today()->startOfDay())
            ->where('created_at', '<=', Carbon::now());
    }

    /**
     * 修复日志数
     */
    public function repairLogCount(): void
    {
        $logCount = $this->logs()->whereNotNull('trade_id')->count();
        $this->updateQuietly(['log_count' => $logCount]);
    }
}
