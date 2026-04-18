<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admin\StoreAdminRoleRequest;
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
class PermissionController extends Controller
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
            $items = AdminPermission::query()->orderBy('id')->paginate($perPage);

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
        $prefix = (string) config('admin.route.prefix');

        $container = collect();

        $routes = collect(app('router')->getRoutes())->map(function ($route) use ($prefix, $container) {
            if (! Str::startsWith($uri = $route->uri(), $prefix) && $prefix && $prefix !== '/') {
                return;
            }

            if (! Str::contains($uri, '{')) {
                if ($prefix !== '/') {
                    $route = Str::replaceFirst($prefix, '', $uri.'*');
                } else {
                    $route = $uri.'*';
                }

                if ($route !== '*') {
                    $container->push($route);
                }
            }

            $path = preg_replace('/{.*}+/', '*', $uri);

            if ($prefix !== '/') {
                return Str::replaceFirst($prefix, '', $path);
            }

            return $path;
        });

        return $container->merge($routes)->filter()->all();
    }

    /**
     * Get options of HTTP methods select field.
     *
     * @return array
     */
    protected function getHttpMethodsOptions(): array
    {
        return config('admin.route.http_methods');
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
    public function store(StoreAdminRoleRequest $request)
    {
        AdminPermission::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminPermission $permission)
    {
        return view('admin.permission.edit', [
            'item' => $permission,
            'update_url' => route('admin.permissions.update', $permission->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAdminRoleRequest $request, AdminPermission $permission)
    {
        $permission->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdminPermission $permission): JsonResponse
    {
        if ($permission->id == 1) {
            return $this->fail(trans('system.default_role_cannot_delete'));
        }
        $permission->delete();

        return $this->success(trans('system.delete_success'));
    }
}
