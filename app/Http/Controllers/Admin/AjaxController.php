<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

namespace App\Http\Controllers\Admin;

use App\Models\Admin\AdminMenu;
use App\Support\TreeHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ajax 接口
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AjaxController extends AbstractController
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
        //$rules = PermissionHelper::getRules($request->user()->getRoleIds());
        //$types = $request->query('type', '0,1');
        //$types = is_string($types) ? explode(',', $types) : [0, 1];
        $items = AdminMenu::query()->orderByDesc('order')->orderBy('id')->get()->toArray();

        $formattedItems = [];
        foreach ($items as $item) {
            $item['parent_id'] = (int) $item['parent_id'];
            $item['name'] = $item['title'];
            $item['value'] = $item['id'];
            $item['icon'] = $item['icon'] ? "layui-icon {$item['icon']}" : '';
            $formattedItems[] = $item;
        }

        $tree = new TreeHelper($formattedItems);
        $tree_items = $tree->getTree();
        // 超级管理员权限为 *
//        if (!in_array('*', $rules)) {
//            PermissionHelper::removeNotContain($tree_items, 'id', $rules);
//        }
//        PermissionHelper::removeNotContain($tree_items, 'type', $types);
//        $menus = PermissionHelper::emptyFilter(TreeHelper::arrayValues($tree_items));
//        if (!app()->environment('production')) {
//            $menus = array_merge($menus, AdminMenu::getDefaultMenus());
//        }

        return response()->json($tree_items);
    }

    /**
     * 获取权限
     */
    public function permission(Request $request): JsonResponse
    {
        $permissions = PermissionHelper::getPermissions($request->user());

        return $this->success('ok', $permissions);
    }


}
