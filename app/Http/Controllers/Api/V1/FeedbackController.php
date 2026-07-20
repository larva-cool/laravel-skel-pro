<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Feedback\StoreFeedbackRequest;
use App\Http\Resources\Api\V1\FeedbackResource;
use App\Models\Feedback\Feedback;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * 用户反馈控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class FeedbackController extends Controller
{
    /**
     * FeedbackController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 我的反馈列表
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = Feedback::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(per_page($request));

        return FeedbackResource::collection($items);
    }

    /**
     * 提交反馈
     */
    public function store(StoreFeedbackRequest $request): FeedbackResource
    {
        $feedback = Feedback::create($request->validated());

        return new FeedbackResource($feedback);
    }

    /**
     * 反馈详情
     *
     * @throws AuthorizationException
     */
    public function show(Request $request, Feedback $feedback): FeedbackResource
    {
        if ($feedback->user_id !== $request->user()->id) {
            throw new AuthorizationException;
        }

        return new FeedbackResource($feedback);
    }
}
