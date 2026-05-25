<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enum\UserType;
use App\Http\Requests\Admin\Announcement\StoreAnnouncementRequest;
use App\Http\Requests\Admin\Announcement\UpdateAnnouncementRequest;
use App\Http\Requests\SwitchRequest;
use App\Http\Resources\Admin\AnnouncementResource;
use App\Models\Announcement\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 公告控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AnnouncementController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:announcements.index')->only(['index']);
        $this->middleware('permission:announcements.create')->only(['create', 'store']);
        $this->middleware('permission:announcements.edit')->only(['edit', 'update']);
        $this->middleware('permission:announcements.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $items = Announcement::query()->orderByDesc('id')->paginate(per_page($request));

            return AnnouncementResource::collection($items);
        }

        return view('admin.announcement.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $coverageOptions = UserType::options();

        return view('admin.announcement.create', [
            'coverage_options' => $coverageOptions,
            'effective_time_type_options' => [
                0 => '永久有效',
                1 => '定时有效',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        Announcement::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.announcement.edit', [
            'item' => $announcement,
            'coverage_options' => UserType::options(),
            'effective_time_type_options' => [
                0 => '永久有效',
                1 => '定时有效',
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $announcement->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * 更新状态
     *
     * @return JsonResponse
     */
    public function updateStatus(SwitchRequest $request)
    {
        $dict = Announcement::query()->where('id', $request->id)->firstOrFail();
        $dict->update($request->safe()->only('status'));

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return $this->success(trans('system.delete_success'));
    }
}
