<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\StoreCollectionRequest;
use App\Http\Resources\Api\V1\CollectionResource;
use App\Models\Content\Collection;
use Illuminate\Http\Request;

/**
 * 收藏控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CollectionController extends Controller
{
    /**
     * CollectionController Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 收藏列表
     */
    public function index(Request $request)
    {
        $perPage = clamp($request->query('per_page', 15), 1, 100);
        $query = Collection::with(['source'])
            ->where('user_id', $request->user()->id);
        if ($request->filled('type')) {
            $query->where('source_type', $request->query('type'));
        }

        $items = $query->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return CollectionResource::collection($items);
    }

    /**
     * 添加收藏
     */
    public function store(StoreCollectionRequest $request)
    {
        if (! Collection::isExist($request->user()->id, $request->source_type, $request->source_id)) {
            Collection::create($request->validated());
        }

        return response()->json(['message' => __('system.collection_success')]);
    }

    /**
     * 取消收藏
     */
    public function destroy(Request $request, Collection $collection)
    {
        if ($collection->user_id == $request->user()->id) {
            $collection?->delete();
        }

        return response()->json(['message' => __('system.collection_cancel_success')]);
    }
}
