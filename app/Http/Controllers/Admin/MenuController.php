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
     * 菜单列表
     */
    public function index(Request $request): JsonResponse
    {
        // onlyEnabled=true：只返回启用的菜单
        $menus = AdminMenu::tree(false);

        $routes = $menus
            ->filter()
            ->values()
            ->all();

        return response()->json($routes);
    }

    /**
     * 获取前端路由配置（按当前管理员角色过滤）
     *
     * 返回 AppRouteRecord[] 格式，前端可直接用于动态路由注册
     */
    public function routes(Request $request): JsonResponse
    {
        /** @var \App\Models\Admin\Admin $admin */
        $admin = $request->user();
        $adminRoles = $admin->getRoleNames()->toArray();

        // onlyEnabled=true：只返回启用的菜单
        $menus = AdminMenu::tree(true);

        $routes = $menus
            ->map(fn (AdminMenu $menu): ?array => $menu->toRouteRecord($adminRoles))
            ->filter()
            ->values()
            ->all();

        return response()->json($routes);
    }

    /**
     * 创建菜单
     */
    public function store(MenuSaveRequest $request): JsonResponse
    {
        $menu = AdminMenu::create($request->validated());

        return response()->json(new MenuResource($menu), 201);
    }

    /**
     * 获取菜单详情
     */
    public function show(string $id): MenuResource
    {
        return new MenuResource(AdminMenu::findOrFail((int) $id));
    }

    /**
     * 更新菜单
     */
    public function update(MenuSaveRequest $request, string $id): MenuResource
    {
        $menu = AdminMenu::findOrFail((int) $id);

        $parentId = (int) $request->validated('parent_id');
        if ($parentId !== 0 && $this->isDescendantOrSelf($menu, $parentId)) {
            abort(422, __('admin.menu_invalid_parent'));
        }

        $menu->update($request->validated());

        return new MenuResource($menu);
    }

    /**
     * 删除菜单
     */
    public function destroy(string $id): JsonResponse
    {
        $menu = AdminMenu::findOrFail((int) $id);

        if ($menu->children()->exists()) {
            abort(400, __('admin.menu_has_children'));
        }

        $menu->delete();

        return response()->json(status: 204);
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
