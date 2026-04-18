<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\SearchAdminRequest;
use App\Http\Requests\Admin\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminPasswordRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminPersonRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminRequest;
use App\Http\Requests\Admin\User\UpdateAvatarRequest;
use App\Http\Requests\SwitchRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin\Admin;
use App\Support\UserHelper;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 管理员管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AdminController extends AbstractController
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
    public function index(SearchAdminRequest $request)
    {
        if ($request->expectsJson()) {
            $perPage = per_page($request, 15);

            // 基础查询
            $query = Admin::query()->with('roles');

            if ($request->filled('keyword')) {
                $query->whereAny(['name', 'username', 'email', 'phone'], 'like', '%'.$request->keyword.'%');
            }
            if ($request->filled('last_login_at') && $request->last_login_at[0] && $request->last_login_at[1]) {
                $query->whereBetween('last_login_at', $request->last_login_at);
            }
            // 动态排序
            if ($request->filled('field') && $request->filled('order')) {
                $query->orderBy($request->field, $request->order);
            }
            // 获取分页数据
            $items = $query->paginate($perPage);

            return AdminResource::collection($items);
        }

        return view('admin.admin.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.admin.create');
    }

    /**
     * 个人信息
     *
     * @return Factory|View|Application|object
     */
    public function person(Request $request)
    {
        return view('admin.admin.person', ['admin' => $request->user('admin')]);
    }

    /**
     * 个人信息保存
     */
    public function storePerson(UpdateAdminPersonRequest $request): JsonResponse
    {
        $admin = $request->user('admin');
        $admin->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * 更新密码
     */
    public function storePassword(UpdateAdminPasswordRequest $request): JsonResponse
    {
        return $this->success(trans('system.update_success'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        $admin = Admin::create($request->safe()->except('roles'));
        $admin->roles()->attach($request->roles);

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Admin $admin)
    {
        return view('admin.admin.edit', ['item' => $admin]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        $admin->update($request->safe()->except('roles'));
        $admin->roles()->sync($request->roles);

        return $this->success(trans('system.update_success'));
    }

    /**
     * 更新状态
     */
    public function updateStatus(SwitchRequest $request): JsonResponse
    {
        $admin = Admin::findOrFail($request->id);
        $admin->update($request->safe()->only('status'));

        return $this->success(trans('system.update_success'));
    }

    /**
     * 更新头像
     */
    public function updateAvatar(UpdateAvatarRequest $request, Admin $admin): JsonResponse
    {
        $filepath = UserHelper::setAvatar($admin->user, $request->file('file'));

        return $this->success(trans('system.update_success'), [
            'file_path' => $filepath,
            'file_url' => $admin->avatar.'?time='.time(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin): JsonResponse
    {
        if (Admin::count() == 1) {
            return $this->fail(trans('system.last_admin_cant_delete'));
        }
        $admin->delete();

        return $this->success(trans('system.delete_success'));
    }
}
