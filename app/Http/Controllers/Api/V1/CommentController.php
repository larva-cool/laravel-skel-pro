<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\StoreCommentRequest;
use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Content\Comment;
use Illuminate\Http\Request;

/**
 * 评论控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CommentController extends Controller
{
    /**
     * CommentController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index']);
        $this->authorizeResource(Comment::class, 'comment');
    }

    /**
     * 评论列表
     */
    public function index(Request $request, $source_type, $source_id)
    {
        $perPage = per_page($request);
        $query = Comment::with(['user'])
            ->where('source_type', $source_type)
            ->where('source_id', $source_id);

        $items = $query->orderByDesc('is_top')
            ->orderByDesc('id')
            ->paginate($perPage);

        return CommentResource::collection($items);
    }

    /**
     * 评论
     */
    public function store(StoreCommentRequest $request)
    {
        $comment = Comment::create($request->validated());

        return new CommentResource($comment);
    }

    /**
     * 删除评论
     */
    public function destroy(Comment $comment)
    {
        $comment?->delete();

        return response()->json(['message' => __('system.delete_success')], 204);
    }
}
