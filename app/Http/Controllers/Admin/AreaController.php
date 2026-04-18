<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Area\StoreAreaRequest;
use App\Http\Requests\Admin\Area\UpdateAreaRequest;
use App\Http\Resources\Admin\AreaResource;
use App\Models\System\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 地区管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AreaController extends AbstractController
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
            $query = Area::query()->orderBy('order')->orderBy('id');
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->integer('parent_id'));
            } else {
                $query->whereNull('parent_id');
            }
            $items = $query->withCount(['children'])->paginate($perPage);

            return AreaResource::collection($items);
        }

        return view('admin.area.index');
    }

    /**
     * 获取 Xm-select 菜单数据
     */
    public function select(Request $request)
    {
        $items = Area::getTreeForXmSelect();

        return response()->json($items);
    }

    /**
     * 获取子菜单（为空、0则获取顶级菜单）
     */
    public function children(Request $request)
    {
        $items = Area::getAreas($request->id, ['id', 'name']);

        return response()->json($items);
    }

    /**
     * 添加菜单页
     */
    public function create()
    {
        return view('admin.area.create');
    }

    /**
     * 添加菜单
     */
    public function store(StoreAreaRequest $request): JsonResponse
    {
        Area::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        return view('admin.area.edit', [
            'item' => $area,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAreaRequest $request, Area $area)
    {
        $area->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area): JsonResponse
    {
        $area->delete();

        return $this->success(trans('system.delete_success'));
    }
}
