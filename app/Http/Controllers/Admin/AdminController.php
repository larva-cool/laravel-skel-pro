<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\AdminCreateRequest;
use App\Http\Requests\Admin\Admin\AdminUpdateRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 后台管理员账号管理控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AdminController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * 管理员列表（分页）
     */
    public function index(Request $request): JsonResponse
    {
        $current = (int) $request->integer('current', 1);
        $size = per_page($request);

        $query = Admin::query()->with('roles');

        if ($keyword = $request->query('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }
        if ($this->hasQuery($request, 'status')) {
            $query->where('status', (int) $request->query('status'));
        }

        $admins = $query->orderByDesc('id')->paginate($size, ['*'], 'current', $current);

        return $this->success([
            'records' => AdminResource::collection($admins),
            'current' => $admins->currentPage(),
            'size' => $admins->perPage(),
            'total' => $admins->total(),
        ]);
    }

    /**
     * 创建管理员
     */
    public function store(AdminCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);

        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $admin = Admin::create($data);

        if ($roles) {
            $admin->syncRoles($roles);
        }

        return $this->success(new AdminResource($admin->load('roles')), __('admin.create_success'));
    }

    /**
     * 获取管理员详情
     */
    public function show(int $id): JsonResponse
    {
        $admin = Admin::with('roles')->findOrFail($id);

        return $this->success(new AdminResource($admin));
    }

    /**
     * 更新管理员
     */
    public function update(AdminUpdateRequest $request, int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);
        $data = $request->validated();

        // 密码为空则不修改
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $admin->update($data);

        if ($roles !== null) {
            $admin->syncRoles($roles);
        }

        return $this->success(new AdminResource($admin->load('roles')), __('admin.update_success'));
    }

    /**
     * 删除管理员
     */
    public function destroy(int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        // 禁止删除自己
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin && $currentAdmin->id === $admin->id) {
            return $this->error(__('admin.cannot_delete_self'), 403);
        }

        // 超级管理员角色不允许删除
        if ($admin->hasRole('super_admin')) {
            return $this->error(__('admin.cannot_delete_super'), 403);
        }

        $admin->delete();

        return $this->success(null, __('admin.delete_success'));
    }

    /**
     * 获取管理员已分配角色列表
     */
    public function roles(int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        return $this->success($admin->getRoleNames());
    }

    /**
     * 分配管理员角色
     */
    public function assignRoles(Request $request, int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'max:50', 'exists:roles,name'],
        ]);

        $admin->syncRoles($data['roles']);

        return $this->success(null, __('admin.assign_roles_success'));
    }

    /**
     * 启用/禁用管理员
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        $currentAdmin = auth('admin')->user();
        if ($currentAdmin && $currentAdmin->id === $admin->id) {
            return $this->error(__('admin.cannot_disable_self'), 403);
        }

        $admin->status = $admin->status->isActive() ? \App\Enums\AdminStatus::STATUS_DISABLED : \App\Enums\AdminStatus::STATUS_ACTIVE;
        $admin->save();

        return $this->success(null, __('admin.toggle_status_success'));
    }

    /**
     * 重置管理员密码
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        $data = $request->validate([
            'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $admin->resetPassword($data['password']);

        return $this->success(null, __('admin.reset_password_success'));
    }

    /**
     * 判断请求中是否存在指定查询参数（非空字符串）
     */
    protected function hasQuery(Request $request, string $key): bool
    {
        $value = $request->query($key);

        return $value !== null && $value !== '';
    }
}
