<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Setting\SettingSaveRequest;
use App\Http\Resources\Admin\SettingResource;
use App\Models\System\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * 后台配置管理控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class SettingController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        //$this->middleware('permission:settings.index')->only(['index', 'show']);
        //$this->middleware('permission:settings.create')->only(['store']);
        //$this->middleware('permission:settings.edit')->only(['update']);
        //$this->middleware('permission:settings.delete')->only(['destroy']);
    }

    /**
     * 配置列表（分页）
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = per_page($request);

        $query = Setting::query();

        if ($keyword = $request->query('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('key', 'like', "%{$keyword}%");
            });
        }

        if ($castType = $request->query('cast_type')) {
            $query->where('cast_type', $castType);
        }

        $items = $query->orderBy('sort')->orderByDesc('id')->paginate($perPage);

        return SettingResource::collection($items);
    }

    /**
     * 创建配置
     */
    public function store(SettingSaveRequest $request): JsonResponse
    {
        $setting = Setting::create($request->validated());

        return response()->json(new SettingResource($setting), 201);
    }

    /**
     * 获取配置详情
     */
    public function show(int $id): SettingResource
    {
        return new SettingResource(Setting::findOrFail($id));
    }

    /**
     * 更新配置
     */
    public function update(SettingSaveRequest $request, int $id): SettingResource
    {
        $setting = Setting::findOrFail($id);

        $setting->update($request->validated());

        return new SettingResource($setting);
    }

    /**
     * 删除配置
     */
    public function destroy(int $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);

        $setting->delete();

        return response()->json(status: 204);
    }
}
