<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Requests\SwitchRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Models\User\Nickname;
use App\Support\UserHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 用户管理
 */
class UserController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:users.index')->only(['index']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            // 基础查询
            $query = User::query()->with(['profile', 'extra']);
            if ($request->filled('id')) {
                $query->where('id', $request->id);
            }
            if ($request->filled('keyword')) {
                $query->whereAny(['name', 'username', 'email', 'phone'], 'like', '%'.$request->keyword.'%');
            }
            if ($request->filled('last_login_at') && $request->last_login_at[0] && $request->last_login_at[1]) {
                $query->whereHas('extra', function ($query) use ($request) {
                    $query->whereBetween('last_login_at', $request->last_login_at);
                });
            }
            if ($request->filled('created_at') && $request->created_at[0] && $request->created_at[1]) {
                $query->whereBetween('created_at', $request->created_at);
            }
            // 动态排序
            if ($request->filled('sortField') && $request->filled('sortOrder')) {
                $query->orderBy($request->sortField, $request->sortOrder);
            }
            // 获取分页数据
            $items = $query->orderByDesc('id')
                ->paginate(per_page($request, 15));

            return UserResource::collection($items);
        }

        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = UserHelper::createByPhone($request->phone, $request->password);
        $data = $request->safe()->only(['name', 'username', 'email']);
        $data['name'] = $data['name'] ?: Nickname::getRandomNickname();
        $user->update($data);

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.user.edit', [
            'item' => $user,
            'update_url' => route('admin.users.update', $user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->safe()->except(['profile', 'extra']));
        $user->profile->update($request->profile);
        $user->extra->update($request->extra);

        return $this->success(trans('system.update_success'));
    }

    /**
     * 更新用户状态
     */
    public function updateStatus(SwitchRequest $request): JsonResponse
    {
        $user = User::find($request->id);
        $user->update($request->safe()->only('status'));

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->success(trans('system.delete_success'));
    }
}
