<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

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
     * 获取菜单树形结构
     */
    public function tree(): AnonymousResourceCollection
    {
        $menus = AdminMenu::tree();

        return MenuResource::collection($menus);
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
}
