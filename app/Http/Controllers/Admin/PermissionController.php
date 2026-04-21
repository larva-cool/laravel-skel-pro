<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\StoreAdminPermissionRequest;
use App\Http\Resources\Admin\PermissionResource;
use App\Models\Admin\AdminPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * 权限管理
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PermissionController extends AbstractController
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
            $items = AdminPermission::query()->orderBy('id')->paginate(per_page($request, 15));

            return PermissionResource::collection($items);
        }

        return view('admin.permission.index');
    }

    /**
     * 获取路由
     * @return array
     */
    public function getRoutes(): array
    {
        // 1. 后台路由前缀
        $prefix = 'admin';

        // 2. 新建一个空集合，用来存放最终要返回的路由
        $container = collect();

        // 3. 获取 Laravel 全部路由，开始遍历处理
        $routes = collect(app('router')->getRoutes())->map(function ($route) use ($prefix, $container) {

            // ====================== 过滤：只保留后台路由 ======================
            // 如果路由不是以 $prefix 开头，并且前缀不是 /，就跳过这条路由
            if (!Str::startsWith($uri = $route->uri(), $prefix) && $prefix && $prefix !== '/') {
                return;
            }

            // ====================== 处理【无参数路由】 ======================
            // 如果路由里没有 {xxx} 这种动态参数
            if (!Str::contains($uri, '{')) {

                // 如果前缀不是 /，就把前缀去掉，末尾加 *
                // 例如：admin/user → user*
                if ($prefix !== '/') {
                    $route = Str::replaceFirst($prefix, '', $uri.'*');
                } else {
                    // 前缀是 /，直接加 *
                    $route = $uri.'*';
                }

                // 把处理好的路由丢到容器里
                if ($route !== '*') {
                    $container->push($route);
                }
            }

            // ====================== 处理【有参数路由】 ======================
            // 把路由里的 {id} {name} 等动态参数 全部替换成 *
            // 例如：admin/user/{id} → admin/user/*
            $path = preg_replace('/{.*}+/', '*', $uri);

            // 去掉前缀
//            if ($prefix !== '/') {
//                return Str::replaceFirst($prefix, '', $path);
//            }

            // 返回处理好的路径
            return $path;

        });

        // 合并、去重、去空
        $finalRoutes = $container
            ->merge($routes)
            ->filter()
            ->unique()
            ->values();

        return $finalRoutes->map(function ($route) {
            return [
                'name' => $route,  // 显示名称
                'value' => $route // 值
            ];
        })->all();
    }

    /**
     * xm-select 选择器
     */
    public function select(): JsonResponse
    {
        $items = AdminPermission::query()->select(['id as value', 'name'])->orderBy('id')->get();

        return response()->json($items);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.permission.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminPermissionRequest $request)
    {
        $permission = AdminPermission::create($request->safe()->except('menus'));
        $permission->menus()->sync($request->menus);

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminPermission $permission)
    {
        $permission->load('menus');
        return view('admin.permission.edit', [
            'item' => $permission,
            'update_url' => route('admin.permissions.update', $permission->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAdminPermissionRequest $request, AdminPermission $permission)
    {
        $permission->update($request->safe()->except('menus'));
        $permission->menus()->sync($request->menus);

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminPermission $permission): JsonResponse
    {
        $permission->delete();

        return $this->success(trans('system.delete_success'));
    }
}
