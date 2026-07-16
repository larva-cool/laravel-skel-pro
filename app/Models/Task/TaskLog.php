<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Task;

use App\Enums\CoinType;
use App\Models\Model;
use App\Models\User;
use App\Support\CoinHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 任务日志
 *
 * @property int $id ID
 * @property int $task_id 任务ID
 * @property int $group_id 任务组ID
 * @property int $user_id 用户ID
 * @property int $coins 奖励金币
 * @property string $trade_id 交易ID
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property User $user 用户
 * @property Task $task 任务
 * @property TaskGroup $group 任务组
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TaskLog extends Model
{
    use MassPrunable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'task_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group_id', 'task_id', 'user_id', 'coins', 'trade_id',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'trade_id' => null,
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
            'task_id' => 'integer',
            'user_id' => 'integer',
            'coins' => 'integer',
            'trade_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 获取可清理的模型查询。
     */
    public function prunable(): Builder
    {
        return static::query()->whereNull('trade_id')->where('created_at', '<=', now()->subDays(2));
    }

    /**
     * 是否可以领取奖励
     */
    protected function canClaim(): bool
    {
        return $this->trade_id == '';
    }

    /**
     * 处理奖励
     */
    public function handleAwarding(): void
    {
        $desc = '完成'.$this->task->name.'任务的奖励';
        $trade = CoinHelper::incr($this->user_id, $this->coins, $this, CoinType::TYPE_TASK, $desc);
        $this->updateQuietly(['trade_id' => $trade->id]);
        $this->task()->increment('log_count');
        $this->group()->increment('log_count');
    }

    /**
     * 用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 任务
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * 任务组
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TaskGroup::class);
    }
}
