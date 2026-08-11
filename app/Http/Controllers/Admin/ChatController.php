<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Ai\Agents\Assistant;
use App\Http\Requests\Admin\ChatRequest;
use App\Models\Admin\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Models\Conversation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * AI 聊天控制器
 *
 * 提供管理员与 AI 助手的对话能力：会话列表、消息历史、发起对话（支持流式 SSE）。
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class ChatController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * 获取当前管理员的会话列表（分页）
     */
    public function conversations(Request $request): JsonResponse
    {
        /** @var Admin $user */
        $user = $request->user();

        $perPage = per_page($request);

        $conversations = $user->conversations()
            ->withCount('messages')
            ->latest('updated_at')
            ->paginate($perPage);

        return response()->json($conversations);
    }

    /**
     * 获取单个会话的聊天记录
     *
     * 仅允许访问自己参与的会话。
     */
    public function conversation(Request $request, string $conversationId): JsonResponse
    {
        /** @var Admin $user */
        $user = $request->user();

        /** @var Conversation $conversation */
        $conversation = $user->conversations()->find($conversationId);

        if (! $conversation) {
            throw new NotFoundHttpException('会话不存在');
        }

        $messages = $conversation->messages()
            ->oldest('created_at')
            ->get()
            ->map(fn ($message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at?->toDateTimeString(),
            ]);

        return response()->json([
            'id' => $conversation->id,
            'title' => $conversation->title,
            'messages' => $messages,
            'created_at' => $conversation->created_at?->toDateTimeString(),
            'updated_at' => $conversation->updated_at?->toDateTimeString(),
        ]);
    }

    /**
     * 发起对话（同步模式，返回完整回复）
     */
    public function chat(ChatRequest $request): JsonResponse
    {
        /** @var Admin $user */
        $user = $request->user();
        $prompt = $request->input('prompt');
        $conversationId = $request->input('conversation_id');

        $agent = Assistant::make();

        if ($conversationId) {
            // 校验会话归属
            $conversation = $user->conversations()->find($conversationId);
            if (! $conversation) {
                throw new NotFoundHttpException('会话不存在');
            }
            $agent->continue($conversationId, as: $user);
        } else {
            $agent->forUser($user);
        }

        $response = $agent->prompt(
            prompt: $prompt,
            model: 'ep-20260809190412-bkx2g',
            provider: 'volc',
        );

        return response()->json([
            'conversation_id' => $response->conversationId ?? $conversationId,
            'reply' => $response->text,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens ?? 0,
                'output_tokens' => $response->usage->outputTokens ?? 0,
            ],
        ]);
    }

    /**
     * 发起对话（流式 SSE 模式，适合前端打字机效果）
     */
    public function stream(ChatRequest $request): mixed
    {
        /** @var Admin $user */
        $user = $request->user();
        $prompt = $request->input('prompt');
        $conversationId = $request->input('conversation_id');

        $agent = Assistant::make();

        if ($conversationId) {
            $conversation = $user->conversations()->find($conversationId);
            if (! $conversation) {
                throw new NotFoundHttpException('会话不存在');
            }
            $agent->continue($conversationId, as: $user);
        } else {
            $agent->forUser($user);
        }

        return $agent->stream(
            prompt: $prompt,
            model: 'ep-20260809190412-bkx2g',
            provider: 'volc',
        );
    }

    /**
     * 删除会话
     */
    public function destroy(Request $request, string $conversationId): JsonResponse
    {
        /** @var Admin $user */
        $user = $request->user();

        $conversation = $user->conversations()->find($conversationId);

        if (! $conversation) {
            throw new NotFoundHttpException('会话不存在');
        }

        $conversation->delete();

        return response()->json(status: 204);
    }
}
