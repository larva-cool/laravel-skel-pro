<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusSwitch;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TaskGroupResource;
use App\Models\Task\Task;
use App\Models\Task\TaskGroup;
use App\Models\Task\TaskLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * 任务控制器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TaskController extends Controller
{
    /**
     * TaskController Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 任务列表（按分组返回，附带当日完成状态）
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = (int) $request->user()->getAuthIdentifier();

        $items = TaskGroup::query()
            ->where('visibility', 1)
            ->where('status', StatusSwitch::ENABLED->value)
            ->with(['activeTasks' => function ($query) use ($userId) {
                $query->with(['todayLog' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }]);
            }])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return TaskGroupResource::collection($items);
    }

    /**
     * 领取任务奖励
     */
    public function claim(Request $request, Task $task): JsonResponse
    {
        if (! $task->status->isEnabled()) {
            return response()->json(['message' => __('任务已下架')], 422);
        }

        $userId = (int) $request->user()->getAuthIdentifier();

        // 幂等：同一用户当日已完成过则拒绝重复领取
        $exists = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->whereNotNull('trade_id')
            ->exists();

        if ($exists) {
            return response()->json(['message' => __('任务奖励今日已领取')], 422);
        }

        $log = DB::transaction(function () use ($task, $userId) {
            $log = TaskLog::create([
                'group_id' => $task->group_id,
                'task_id' => $task->id,
                'user_id' => $userId,
                'coins' => $task->coins,
            ]);
            $log->setRelation('task', $task);
            $log->handleAwarding();

            return $log;
        });

        return response()->json([
            'message' => __('system.create_success'),
            'data' => [
                'task_id' => $task->id,
                'coins' => $log->coins,
                'trade_id' => $log->trade_id,
            ],
        ]);
    }
}
