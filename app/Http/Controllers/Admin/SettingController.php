<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Setting\StoreConfigRequest;
use App\Http\Requests\Admin\Setting\StoreSettingRequest;
use App\Http\Requests\Admin\Setting\UpdateSettingRequest;
use App\Http\Resources\Admin\SettingResource;
use App\Models\System\Setting;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 配置管理
 */
class SettingController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:settings.index')->only(['index']);
        $this->middleware('permission:settings.create')->only(['create', 'store']);
        $this->middleware('permission:settings.edit')->only(['edit', 'update', 'config', 'storeConfig']);
        $this->middleware('permission:settings.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            // 基础查询
            $query = Setting::query();

            if ($request->filled('keyword')) {
                $query->whereAny(['key', 'name', 'description'], 'like', '%'.$request->keyword.'%');
            }
            // 动态排序
            if ($request->filled('field') && $request->filled('order')) {
                $query->orderBy($request->field, $request->order);
            }
            $query->orderByDesc('id');
            // 获取分页数据
            $items = $query->paginate(per_page($request, 15));

            return SettingResource::collection($items);
        }

        return view('admin.setting.index');
    }

    /**
     * 创建配置
     */
    public function create()
    {
        return view('admin.setting.create');
    }

    /**
     * 保存配置
     */
    public function store(StoreSettingRequest $request)
    {
        Setting::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * 编辑配置
     */
    public function edit(Setting $setting)
    {
        return view('admin.setting.edit', [
            'item' => $setting,
        ]);
    }

    /**
     * 更新配置
     *
     * @return JsonResponse
     */
    public function update(Setting $setting, UpdateSettingRequest $request)
    {
        $setting->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * 删除配置
     */
    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();

        return $this->success('删除成功');
    }

    /**
     * 配置管理
     *
     * @return Factory|View
     */
    public function config()
    {
        $disks = array_keys(config('filesystems.disks'));

        return view('admin.setting.config', [
            'settings' => Setting::getAll(),
            'disks' => $disks,
        ]);
    }

    /**
     * 保存配置
     */
    public function storeConfig(StoreConfigRequest $request): JsonResponse
    {
        $input = Arr::dot($request->validated());
        $updateTime = now();
        $items = [];
        foreach ($input as $key => $val) {
            $items[] = ['key' => $key, 'value' => $val, 'updated_at' => $updateTime];
        }
        Setting::query()->upsert(
            $items,
            ['key'],
            ['value', 'updated_at'],
        );
        settings()->all(true);

        return $this->success('设置完成');
    }
}
