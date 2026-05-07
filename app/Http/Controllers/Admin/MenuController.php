<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\StoreAdminMenuRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminMenuRequest;
use App\Http\Resources\Admin\MenuResource;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use App\Support\TreeHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 菜单管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class MenuController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:menus.index')->only(['index']);
        $this->middleware('permission:menus.create')->only(['create', 'store']);
        $this->middleware('permission:menus.edit')->only(['edit', 'update']);
        $this->middleware('permission:menus.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $perPage = per_page($request, 1000);
            $query = AdminMenu::query()->withCount('children')->orderBy('order')->orderBy('id');
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->integer('parent_id'));
            } else {
                $query->whereNull('parent_id');
            }
            $items = $query->withCount(['children'])->paginate($perPage);

            return MenuResource::collection($items);
        }

        return view('admin.menu.index');
    }

    /**
     * 添加菜单页
     */
    public function create()
    {
        return view('admin.menu.create');
    }

    /**
     * 添加菜单
     */
    public function store(StoreAdminMenuRequest $request): JsonResponse
    {
        AdminMenu::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminMenu $menu)
    {
        return view('admin.menu.edit', [
            'item' => $menu,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminMenuRequest $request, AdminMenu $menu)
    {
        $menu->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminMenu $menu): JsonResponse
    {
        $menu->delete();

        return $this->success(trans('system.delete_success'));
    }

    /**
     * 菜单 Select
     */
    public function menuSelect(Request $request): array
    {
        return AdminMenu::getTreeForXmSelect();
    }

    /**
     * 获取后台左侧菜单列表
     */
    public function leftMenus(Request $request): JsonResponse
    {
        /** @var Admin $user */
        $user = $request->user();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // 获取所有菜单
        $menus = AdminMenu::query();
        // 筛选出有权限的菜单
        if ($permissions) {
            $menus->where(function ($query) use ($permissions) {
                $query->whereNull('permission_name')
                    ->orWhereIn('permission_name', $permissions);
            });
        }
        $items = $menus->orderByDesc('order')->orderBy('id')->get()->toArray();

        $formattedItems = [];
        foreach ($items as $item) {
            $item['parent_id'] = (int) $item['parent_id'];
            $item['name'] = $item['title'];
            $item['value'] = $item['id'];
            $item['icon'] = $item['icon'] ? "layui-icon {$item['icon']}" : '';
            $formattedItems[] = $item;
        }

        $tree = new TreeHelper($formattedItems);
        $treeItems = $tree->getTree();

        if (! app()->environment('production')) {
            $treeItems = array_merge($treeItems, AdminMenu::getDefaultMenus());
        }

        return response()->json($treeItems);
    }
}
