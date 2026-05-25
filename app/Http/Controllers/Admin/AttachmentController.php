<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Attachment\SearchAttachmentRequest;
use App\Http\Requests\Admin\Attachment\StoreAttachmentRequest;
use App\Http\Resources\Admin\AttachmentResource;
use App\Models\System\Attachment;
use Illuminate\Http\JsonResponse;

/**
 * 附件管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AttachmentController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:areas.index')->only(['index']);
        $this->middleware('permission:areas.create')->only(['create', 'store']);
        $this->middleware('permission:areas.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SearchAttachmentRequest $request)
    {
        if ($request->expectsJson()) {
            // 基础查询
            $query = Attachment::query()->with('user');
            if ($request->filled('keyword')) {
                $query->whereAny(['file_name'], 'like', '%'.$request->keyword.'%');
            }
            if ($request->filled('created_at') && $request->created_at[0] && $request->created_at[1]) {
                $query->whereBetween('created_at', $request->created_at);
            }
            // 动态排序
            if ($request->filled('field') && $request->filled('order')) {
                $query->orderBy($request->field, $request->order);
            }

            // 获取分页数据
            $items = $query->paginate(per_page($request, 15));

            return AttachmentResource::collection($items);
        }

        return view('admin.attachment.index');
    }

    /**
     * 附件上传
     */
    public function create()
    {
        return view('admin.attachment.create');
    }

    /**
     * 附件上传
     */
    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        // 处理文件上传
        $fileInfo = $request->handleUpload();
        $attachment = Attachment::create($fileInfo);

        return $this->success('上传成功', [
            'file_name' => $attachment->file_name,
            'file_path' => $attachment->file_path,
            'url' => $attachment->file_url,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $attachment->delete();

        return $this->success(trans('system.delete_success'));
    }
}
