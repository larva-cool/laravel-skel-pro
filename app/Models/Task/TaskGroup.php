<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Task;

use App\Enums\StatusSwitch;
use App\Enums\TaskType;
use App\Models\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 任务组
 *
 * @property int $id ID
 * @property string $name 名称
 * @property string $description 描述
 * @property TaskType $type 类型
 * @property StatusSwitch $status 状态
 * @property int $visibility 可见性
 * @property int $order 排序
 * @property int $log_count 日志数
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property Carbon $deleted_at 删除时间
 * @property-read string $type_name 类别名称
 * @property Collection<int,Task> $tasks 任务
 * @property-read int $tasks_count 任务数
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class TaskGroup extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'task_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'description', 'type', 'status', 'order', 'visibility', 'log_count',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'order' => 0,
        'visibility' => 1,
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
            'name' => 'string',
            'description' => 'string',
            'type' => TaskType::class,
            'status' => StatusSwitch::class,
            'order' => 'integer',
            'visibility' => 'integer',
            'log_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * 获取任务类型名称
     */
    public function getTypeNameAttribute(): string
    {
        return $this->type->label();
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
     * 子任务
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'group_id');
    }

    /**
     * 活动子任务
     */
    public function activeTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'group_id')
            ->where('status', StatusSwitch::ENABLED->value)
            ->orderBy('order');
    }

    /**
     * 日志关系定义
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class, 'group_id');
    }

    /**
     * 今日日志
     */
    public function todayLogs(): HasMany
    {
        return $this->logs()->whereDate('created_at', '=', Carbon::today());
    }

    /**
     * 修复日志数
     */
    public function repairLogCount(): void
    {
        foreach ($this->tasks as $task) {
            $task->repairLogCount();
        }
        $logCount = $this->logs()->whereNotNull('trade_id')->count();
        $this->updateQuietly(['log_count' => $logCount]);
    }
}
