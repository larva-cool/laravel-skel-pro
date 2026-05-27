<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

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
        $this->middleware('permission:areas.index')->only(['index']);
        $this->middleware('permission:areas.create')->only(['create', 'store']);
        $this->middleware('permission:areas.edit')->only(['edit', 'update']);
        $this->middleware('permission:areas.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = Area::query()->orderBy('order')->orderBy('id');
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->integer('parent_id'));
            } else {
                $query->whereNull('parent_id');
            }
            $items = $query->withCount(['children'])->paginate(per_page($request, 1000));

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
        $items = Area::getAreas($request->query('id'), ['id', 'name']);

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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'area_code' => ['nullable', 'integer'],
            'city_code' => ['nullable', 'string', 'regex:/^0\d{2,3}$/'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        Area::create($validated);

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
    public function update(Request $request, Area $area): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'area_code' => ['nullable', 'integer'],
            'city_code' => ['nullable', 'string', 'regex:/^0\d{2,3}$/'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        $area->update($validated);

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
