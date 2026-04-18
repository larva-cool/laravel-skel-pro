<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AnnouncementResource;
use App\Models\Announcement\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * 公告管理
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AnnouncementController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 列表
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = per_page($request);
        $userId = $request->user()->id;
        $items = Announcement::active('user')
            ->with([
                'read' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('user_type', 'user');
                },
            ])
            ->orderByDesc('id')
            ->paginate($perPage);

        return AnnouncementResource::collection($items);
    }

    /**
     * 详情
     */
    public function show(Request $request, Announcement $announcement): AnnouncementResource
    {
        $userId = $request->user()->id;
        $announcement->markAsRead($userId, 'user');
        $announcement->load(['read' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }]);

        return new AnnouncementResource($announcement);
    }
}
