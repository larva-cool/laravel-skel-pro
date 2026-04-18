<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\User\StoreUserGroupRequest;
use App\Http\Requests\Admin\User\UpdateUserGroupRequest;
use App\Http\Resources\Admin\UserGroupResource;
use App\Models\User\UserGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 用户组管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserGroupController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $perPage = per_page($request, 15);
            $items = UserGroup::query()->orderBy('id')->paginate($perPage);

            return UserGroupResource::collection($items);
        }

        return view('admin.user_group.index');
    }

    /**
     * xm-select 选择器
     */
    public function select(): JsonResponse
    {
        $items = UserGroup::query()->select(['id as value', 'name'])->orderBy('id')->get();

        return response()->json($items);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user_group.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserGroupRequest $request)
    {
        UserGroup::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserGroup $user_group)
    {
        return view('admin.user_group.edit', [
            'item' => $user_group,
            'update_url' => route('admin.user_groups.update', $user_group->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserGroupRequest $request, UserGroup $user_group)
    {
        $user_group->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserGroup $user_group): JsonResponse
    {
        if ($user_group->users()->count() > 0) {
            return $this->fail(trans('system.user_group_has_users'));
        }
        $user_group->delete();

        return $this->success(trans('system.delete_success'));
    }
}
