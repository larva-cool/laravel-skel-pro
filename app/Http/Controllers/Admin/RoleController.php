<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\StoreAdminRoleRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Admin\AdminRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 角色管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class RoleController extends AbstractController
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
            $items = AdminRole::query()->orderBy('id')->paginate(per_page($request, 15));

            return RoleResource::collection($items);
        }

        return view('admin.role.index');
    }

    /**
     * xm-select 选择器
     */
    public function select(): JsonResponse
    {
        $items = AdminRole::query()->select(['id as value', 'name'])->orderBy('id')->get();

        return response()->json($items);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.role.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRoleRequest $request)
    {
        AdminRole::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminRole $role)
    {
        return view('admin.role.edit', [
            'item' => $role,
            'update_url' => route('admin.roles.update', $role->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAdminRoleRequest $request, AdminRole $role)
    {
        $role->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminRole $role): JsonResponse
    {
        if ($role->id == 1) {
            return $this->fail(trans('system.default_role_cannot_delete'));
        }
        $role->delete();

        return $this->success(trans('system.delete_success'));
    }
}
