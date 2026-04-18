<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\StoreLikeRequest;
use App\Http\Resources\Api\V1\LikeResource;
use App\Models\Content\Like;
use Illuminate\Http\Request;

/**
 * 点赞控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class LikeController extends Controller
{
    /**
     * LikeController Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 我的点赞
     */
    public function index(Request $request)
    {
        $perPage = \per_page($request);
        $query = Like::with(['source'])->where('user_id', $request->user()->id);
        if ($request->filled('type')) {
            $query->where('source_type', $request->query('type'));
        }

        $items = $query->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return LikeResource::collection($items);
    }

    /**
     * 点赞
     */
    public function store(StoreLikeRequest $request)
    {
        if (! Like::isExist($request->user_id, $request->source_type, $request->source_id)) {
            Like::create($request->validated());
        } else {
            return response()->json(['message' => __('system.like_exist')], 400);
        }

        return response()->json(['message' => __('system.like_success')]);
    }

    /**
     * 取消点赞
     */
    public function destroy(Request $request, Like $like)
    {
        if ($like->user_id == $request->user()->id) {
            $like?->delete();
        }

        return response()->json(['message' => __('system.like_cancel_success')]);
    }
}
