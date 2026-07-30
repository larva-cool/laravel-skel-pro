<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Menu\MenuSaveRequest;
use App\Http\Resources\Admin\MenuResource;
use App\Models\Admin\AdminMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 后台菜单管理控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class MenuController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * 菜单列表（分页）
     */
    public function index(Request $request): JsonResponse
    {
        $query = AdminMenu::query();

        if ($title = $request->query('title')) {
            $query->where('title', 'like', "%{$title}%");
        }
        if ($this->hasQuery($request, 'type')) {
            $query->where('type', (int) $request->query('type'));
        }
        if ($this->hasQuery($request, 'is_enable')) {
            $query->where('is_enable', (bool) $request->query('is_enable'));
        }

        $menus = $query->ordered()->get();

        return $this->success(MenuResource::collection($menus));
    }

    /**
     * 获取菜单树形结构
     */
    public function tree(): JsonResponse
    {
        $menus = AdminMenu::tree();

        return $this->success(MenuResource::collection($menus));
    }

    /**
     * 创建菜单
     */
    public function store(MenuSaveRequest $request): JsonResponse
    {
        $menu = AdminMenu::create($request->validated());

        return $this->success(new MenuResource($menu), __('admin.menu.create_success'));
    }

    /**
     * 获取菜单详情
     */
    public function show(int $id): JsonResponse
    {
        $menu = AdminMenu::findOrFail($id);

        return $this->success(new MenuResource($menu));
    }

    /**
     * 更新菜单
     */
    public function update(MenuSaveRequest $request, int $id): JsonResponse
    {
        $menu = AdminMenu::findOrFail($id);

        // 不允许将父级设为自身或自身的子级
        $parentId = (int) $request->validated('parent_id');
        if ($parentId !== 0 && $this->isDescendantOrSelf($menu, $parentId)) {
            return $this->error(__('admin.menu.invalid_parent'), 422);
        }

        $menu->update($request->validated());

        return $this->success(new MenuResource($menu), __('admin.menu.update_success'));
    }

    /**
     * 删除菜单
     */
    public function destroy(int $id): JsonResponse
    {
        $menu = AdminMenu::findOrFail($id);

        // 存在子菜单时不允许删除
        if ($menu->children()->exists()) {
            return $this->error(__('admin.menu.has_children'));
        }

        $menu->delete();

        return $this->success(null, __('admin.menu.delete_success'));
    }

    /**
     * 判断目标菜单是否为当前菜单的自身或后代
     */
    protected function isDescendantOrSelf(AdminMenu $menu, int $targetId): bool
    {
        if ($menu->id === $targetId) {
            return true;
        }

        return $menu->children->contains(
            fn (AdminMenu $child): bool => $this->isDescendantOrSelf($child, $targetId)
        );
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
