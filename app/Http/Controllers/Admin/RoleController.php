<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Role\RoleSaveRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Admin\AdminMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * 后台角色管理控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class RoleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * 角色列表（分页）
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = per_page($request);

        $query = Role::where('guard_name', AdminMenu::GUARD_NAME);

        if ($name = $request->query('role_name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        $items = $query->orderByDesc('id')->paginate($perPage);

        return RoleResource::collection($items);
    }

    /**
     * 创建角色
     */
    public function store(RoleSaveRequest $request): JsonResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => AdminMenu::GUARD_NAME,
        ]);

        $permissions = $request->validated('permissions', []);
        if ($permissions) {
            $role->syncPermissions($permissions);
        }

        return response()->json(new RoleResource($role), 201);
    }

    /**
     * 获取角色详情
     */
    public function show(string $id): RoleResource
    {
        return new RoleResource($this->findRole($id));
    }

    /**
     * 更新角色
     */
    public function update(RoleSaveRequest $request, string $id): RoleResource
    {
        $role = $this->findRole($id);

        if ($role->name === 'super_admin') {
            abort(403, __('admin.role_cannot_modify_super'));
        }

        $role->update(['name' => $request->validated('name')]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->validated('permissions'));
        }

        return new RoleResource($role);
    }

    /**
     * 删除角色
     */
    public function destroy(string $id): JsonResponse
    {
        $role = $this->findRole($id);

        if ($role->name === 'super_admin') {
            abort(403, __('admin.role_cannot_delete_super'));
        }

        if ($role->users()->count() > 0) {
            abort(400, __('admin.role_in_use'));
        }

        $role->delete();

        return response()->json(status: 204);
    }

    /**
     * 获取角色已分配的权限 ID 列表
     */
    public function permissions(string $id): JsonResponse
    {
        $role = $this->findRole($id);

        return response()->json($role->permissions()->pluck('id'));
    }

    /**
     * 分配角色权限
     */
    public function assignPermissions(Request $request, string $id): JsonResponse
    {
        $role = $this->findRole($id);

        if ($role->name === 'super_admin') {
            abort(403, __('admin.role_cannot_modify_super'));
        }

        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $validPermissionIds = Permission::whereIn('id', $data['permissions'])
            ->where('guard_name', AdminMenu::GUARD_NAME)
            ->pluck('id')
            ->toArray();

        $role->syncPermissions($validPermissionIds);

        return response()->json($role->permissions()->pluck('id'));
    }

    /**
     * 获取所有权限列表（用于权限分配）
     */
    public function allPermissions(): JsonResponse
    {
        $permissions = Permission::where('guard_name', AdminMenu::GUARD_NAME)
            ->orderBy('id')
            ->get(['id', 'name', 'display_name']);

        return response()->json($permissions);
    }

    /**
     * 根据 ID 获取角色
     */
    private function findRole(string $id): Role
    {
        return Role::where('guard_name', AdminMenu::GUARD_NAME)->findOrFail((int) $id);
    }
}
