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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
    public function index(Request $request): AnonymousResourceCollection
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

        $items = $query->ordered()->get();

        return MenuResource::collection($items);
    }

    /**
     * 获取菜单树形结构
     */
    public function tree(): AnonymousResourceCollection
    {
        $menus = AdminMenu::tree();

        return MenuResource::collection($menus);
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
    public function show(int $id): MenuResource
    {
        return new MenuResource(AdminMenu::findOrFail($id));
    }

    /**
     * 更新菜单
     */
    public function update(MenuSaveRequest $request, int $id): MenuResource
    {
        $menu = AdminMenu::findOrFail($id);

        $parentId = (int) $request->validated('parent_id');
        if ($parentId !== 0 && $this->isDescendantOrSelf($menu, $parentId)) {
            abort(422, __('admin.menu.invalid_parent'));
        }

        $menu->update($request->validated());

        return new MenuResource($menu);
    }

    /**
     * 删除菜单
     */
    public function destroy(int $id): JsonResponse
    {
        $menu = AdminMenu::findOrFail($id);

        if ($menu->children()->exists()) {
            abort(400, __('admin.menu.has_children'));
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
