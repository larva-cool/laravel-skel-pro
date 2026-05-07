<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use App\Support\TreeHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API 接口
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ApiController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * 获取菜单列表
     */
    public function leftMenus(Request $request)
    {
        /** @var Admin $user */
        $user = $request->user();
        // $rules = PermissionHelper::getRules($request->user()->getRoleIds());
        // $types = $request->query('type', '0,1');
        // $types = is_string($types) ? explode(',', $types) : [0, 1];

        // 读取所有菜单
        $formattedItems = AdminMenu::getLeftMenus();

        $tree = new TreeHelper($formattedItems);
        $tree_items = $tree->getTree();
        // 检查用户是否是超管，超管加载所有菜单
        if (! $user->isAdministrator()) {
            // 删除无权限的菜单
        }
        // 超级管理员权限为 *
        //        if (!in_array('*', $rules)) {
        //            PermissionHelper::removeNotContain($tree_items, 'id', $rules);
        //        }
        //        PermissionHelper::removeNotContain($tree_items, 'type', $types);
        //        $menus = PermissionHelper::emptyFilter(TreeHelper::arrayValues($tree_items));
        $menus = $tree_items;
        if (! app()->environment('production')) {
            $menus = array_merge($menus, AdminMenu::getDefaultMenus());
        }

        return response()->json($menus);
    }

    /**
     * 检查权限
     */
    public function permission(Request $request): JsonResponse
    {
        $permissions = PermissionHelper::getPermissions($request->user());

        return $this->success('ok', $permissions);
    }
}
