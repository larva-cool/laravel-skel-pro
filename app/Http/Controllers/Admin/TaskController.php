<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Task\StoreTaskRequest;
use App\Http\Requests\Admin\Task\UpdateTaskRequest;
use App\Http\Resources\Admin\TaskResource;
use App\Models\Task\Task;
use App\Models\Task\TaskGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 任务管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class TaskController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:tasks.index')->only(['index']);
        $this->middleware('permission:tasks.create')->only(['create', 'store']);
        $this->middleware('permission:tasks.edit')->only(['edit', 'update']);
        $this->middleware('permission:tasks.delete')->only(['destroy']);
    }

    /**
     * 任务列表
     */
    public function index(Request $request, TaskGroup $task_group)
    {
        if ($request->expectsJson()) {
            $items = $task_group->tasks()
                ->with(['group'])
                ->orderBy('id')
                ->paginate(per_page($request));

            return TaskResource::collection($items);
        }

        return view('admin.task.index', [
            'group' => $task_group,
            'tasks_url' => route('admin.task_groups.tasks.index', ['task_group' => $task_group]),
            'create_url' => route('admin.task_groups.tasks.create', ['task_group' => $task_group]),
            'repair_url' => route('admin.task_groups.repair', ['task_group' => $task_group]),
        ]);
    }

    /**
     * 创建任务
     */
    public function create(Request $request, TaskGroup $task_group)
    {
        return view('admin.task.create', [
            'group' => $task_group,
            'store_url' => route('admin.task_groups.tasks.index', ['task_group' => $task_group]),
        ]);
    }

    /**
     * @param  StoreTaskRequest  $request
     * @param  TaskGroup  $task_group
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request, TaskGroup $task_group): JsonResponse
    {
        $task_group->tasks()->create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * 编辑任务
     */
    public function edit(TaskGroup $task_group, Task $task)
    {
        return view('admin.task.edit', [
            'item' => $task,
            'update_url' => route('admin.task_groups.tasks.update', ['task_group' => $task_group, 'task' => $task]),
        ]);
    }

    /**
     * 更新任务
     */
    public function update(UpdateTaskRequest $request, TaskGroup $task_group, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * 删除任务
     */
    public function destroy(TaskGroup $task_group, Task $task): JsonResponse
    {
        if ($task->logs()->count() > 0) {
            return $this->fail('该任务下存在完成记录，不能删除');
        }
        $task->delete();

        return $this->success(trans('system.delete_success'));
    }
}
