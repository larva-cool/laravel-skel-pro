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
    public function index(Request $request): JsonResponse
    {
        $current = (int) $request->integer('current', 1);
        $size = per_page($request);

        $query = Role::where('guard_name', AdminMenu::GUARD_NAME);

        if ($name = $request->query('role_name')) {
            $query->where('name', 'like', "%{$name}%");
        }
        if ($code = $request->query('role_code')) {
            $query->where('name', 'like', "%{$code}%");
        }

        $roles = $query->orderByDesc('id')->paginate($size, ['*'], 'current', $current);

        return $this->success([
            'records' => RoleResource::collection($roles),
            'current' => $roles->currentPage(),
            'size' => $roles->perPage(),
            'total' => $roles->total(),
        ]);
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

        return $this->success(new RoleResource($role), __('admin.role.create_success'));
    }

    /**
     * 获取角色详情
     */
    public function show(int $id): JsonResponse
    {
        $role = $this->findRole($id);

        return $this->success(new RoleResource($role));
    }

    /**
     * 更新角色
     */
    public function update(RoleSaveRequest $request, int $id): JsonResponse
    {
        $role = $this->findRole($id);

        // 超级管理员角色不允许修改名称
        if ($role->name === 'super_admin') {
            return $this->error(__('admin.role.cannot_modify_super'), 403);
        }

        $role->update(['name' => $request->validated('name')]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->validated('permissions'));
        }

        return $this->success(new RoleResource($role), __('admin.role.update_success'));
    }

    /**
     * 删除角色
     */
    public function destroy(int $id): JsonResponse
    {
        $role = $this->findRole($id);

        if ($role->name === 'super_admin') {
            return $this->error(__('admin.role.cannot_delete_super'), 403);
        }

        // 检查是否有管理员使用此角色
        if ($role->users()->count() > 0) {
            return $this->error(__('admin.role.in_use'));
        }

        $role->delete();

        return $this->success(null, __('admin.role.delete_success'));
    }

    /**
     * 获取角色已分配的权限 ID 列表
     */
    public function permissions(int $id): JsonResponse
    {
        $role = $this->findRole($id);

        return $this->success($role->permissions()->pluck('id'));
    }

    /**
     * 分配角色权限
     */
    public function assignPermissions(Request $request, int $id): JsonResponse
    {
        $role = $this->findRole($id);

        if ($role->name === 'super_admin') {
            return $this->error(__('admin.role.cannot_modify_super'), 403);
        }

        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // 校验权限 guard 是否匹配
        $validPermissionIds = Permission::whereIn('id', $data['permissions'])
            ->where('guard_name', AdminMenu::GUARD_NAME)
            ->pluck('id')
            ->toArray();

        $role->syncPermissions($validPermissionIds);

        return $this->success(null, __('admin.role.assign_permissions_success'));
    }

    /**
     * 获取所有权限列表（用于权限分配）
     */
    public function allPermissions(): JsonResponse
    {
        $permissions = Permission::where('guard_name', AdminMenu::GUARD_NAME)
            ->orderBy('id')
            ->get(['id', 'name', 'display_name']);

        return $this->success($permissions);
    }

    /**
     * 根据 ID 获取角色
     */
    private function findRole(int $id): Role
    {
        return Role::where('guard_name', AdminMenu::GUARD_NAME)->findOrFail($id);
    }
}
