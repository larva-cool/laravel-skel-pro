<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Http\Requests\Admin\Feedback\ReplyFeedbackRequest;
use App\Http\Resources\Admin\FeedbackResource;
use App\Models\Feedback\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 反馈管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class FeedbackController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:feedbacks.index')->only(['index']);
        $this->middleware('permission:feedbacks.edit')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('permission:feedbacks.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = Feedback::query()->with('user');

            if ($type = $request->string('type')->toString()) {
                $query->where('type', $type);
            }
            if ($status = $request->string('status')->toString()) {
                $query->where('status', $status);
            }
            if ($keyword = $request->string('keyword')->toString()) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('content', 'like', "%{$keyword}%");
                });
            }

            $items = $query->orderByDesc('id')->paginate(per_page($request));

            return FeedbackResource::collection($items);
        }

        return view('admin.feedback.index', [
            'type_options' => FeedbackType::options(),
            'status_options' => FeedbackStatus::options(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feedback $feedback)
    {
        $feedback->load('user');

        return view('admin.feedback.edit', [
            'item' => $feedback,
            'status_options' => FeedbackStatus::options(),
            'update_url' => route('admin.feedbacks.update', $feedback->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReplyFeedbackRequest $request, Feedback $feedback): JsonResponse
    {
        $validated = $request->validated();
        $status = isset($validated['status'])
            ? FeedbackStatus::from($validated['status'])
            : FeedbackStatus::REPLIED;

        $feedback->replyByAdmin($request->user('admin'), $validated['reply'], $status);

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback): JsonResponse
    {
        $feedback->delete();

        return $this->success(trans('system.delete_success'));
    }
}
