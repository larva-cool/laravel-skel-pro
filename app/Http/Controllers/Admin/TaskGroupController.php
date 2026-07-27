<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TaskType;
use App\Http\Requests\Admin\Task\StoreTaskGroupRequest;
use App\Http\Requests\Admin\Task\UpdateTaskGroupRequest;
use App\Http\Resources\Admin\TaskGroupResource;
use App\Jobs\Task\RepairLogCountJob;
use App\Models\Task\TaskGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 任务分组管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class TaskGroupController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:task_groups.index')->only(['index']);
        $this->middleware('permission:task_groups.create')->only(['create', 'store']);
        $this->middleware('permission:task_groups.edit')->only(['edit', 'update']);
        $this->middleware('permission:task_groups.delete')->only(['destroy']);
    }

    /**
     * 任务分组列表
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $items = TaskGroup::query()
                ->withCount(['tasks'])
                ->orderBy('id')
                ->paginate(per_page($request));

            return TaskGroupResource::collection($items);
        }

        return view('admin.task_group.index');
    }

    /**
     * 任务分组创建
     */
    public function create()
    {
        return view('admin.task_group.create', [
            'taskTypes' => TaskType::options(),
        ]);
    }

    /**
     * 任务分组创建
     */
    public function store(StoreTaskGroupRequest $request): JsonResponse
    {
        TaskGroup::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * 任务分组编辑
     */
    public function edit(TaskGroup $taskGroup)
    {
        return view('admin.task_group.edit', [
            'item' => $taskGroup,
        ]);
    }

    /**
     * 任务分组更新
     */
    public function update(UpdateTaskGroupRequest $request, TaskGroup $taskGroup): JsonResponse
    {
        $taskGroup->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * 更新状态
     */
    public function updateStatus(Request $request, TaskGroup $taskGroup): JsonResponse
    {
        $validated = $request->validate([
            'status' => [
                'sometimes', 'required', 'in:0,1',
            ],
        ]);
        $taskGroup->update(['status' => $validated['status']]);

        return $this->success(trans('system.update_success'));
    }

    /**
     * 修复
     */
    public function repair(TaskGroup $task_group): JsonResponse
    {
        foreach ($task_group->tasks as $task) {
            RepairLogCountJob::dispatch($task->id);
        }

        return $this->success(trans('system.update_success'));
    }

    /**
     * 任务分组删除
     */
    public function destroy(TaskGroup $taskGroup): JsonResponse
    {
        if ($taskGroup->tasks()->count() > 0) {
            return $this->fail('该任务分组下存在任务，不能删除');
        }
        $taskGroup->delete();

        return $this->success(trans('system.delete_success'));
    }
}
