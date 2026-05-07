<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\StoreAdminPermissionRequest;
use App\Http\Resources\Admin\PermissionResource;
use App\Models\System\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 权限管理
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PermissionController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('role:Super Admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $perPage = per_page($request, 15);
            $items = Permission::query()->orderByDesc('id')->paginate($perPage);

            return PermissionResource::collection($items);
        }

        return view('admin.permission.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.permission.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminPermissionRequest $request)
    {
        Permission::create($request->safe()->except('menus'));

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('admin.permission.edit', [
            'item' => $permission,
            'update_url' => route('admin.permissions.update', $permission->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAdminPermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($request->safe()->except('menus'));

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return $this->success(trans('system.delete_success'));
    }
}
