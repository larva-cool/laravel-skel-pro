<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\StoreAdminMenuRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminMenuRequest;
use App\Http\Resources\Admin\MenuResource;
use App\Models\Admin\AdminMenu;
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
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $perPage = per_page($request, 15);
            $query = AdminMenu::query()->orderBy('order')->orderBy('id');
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
}
