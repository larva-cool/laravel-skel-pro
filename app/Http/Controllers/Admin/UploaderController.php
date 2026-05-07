<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Attachment\StoreAttachmentRequest;
use App\Http\Requests\Admin\Attachment\UploadFileRequest;
use App\Http\Requests\Admin\Attachment\UploadImageRequest;
use App\Http\Requests\Admin\Attachment\UploadVideoRequest;
use App\Models\System\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * 上传控制器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UploaderController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * AiEditor 上传
     *
     *
     * @throws ValidationException
     */
    public function aiEditorImage(UploadImageRequest $request): JsonResponse
    {
        // 处理文件上传
        $fileInfo = $request->handleUpload();
        $attachment = Attachment::create($fileInfo);

        return response()->json([
            'errorCode' => 0,
            'data' => [
                'src' => $attachment->file_url,
                'alt' => $attachment->file_name,
                'loading' => true,
                'data-src' => $attachment->file_url,
                'align' => 'center',
                'width' => '100%',
                'height' => 'auto',
            ],
        ]);
    }

    /**
     * AiEditor 上传
     *
     * @throws ValidationException
     */
    public function aiEditorVideo(UploadVideoRequest $request): JsonResponse
    {
        // 处理文件上传
        $fileInfo = $request->handleUpload();
        $attachment = Attachment::create($fileInfo);

        return response()->json([
            'errorCode' => 0,
            'data' => [
                'src' => $attachment->file_url,
                'poster' => '',
            ],
        ]);
    }

    /**
     * AiEditor 上传
     *
     * @throws ValidationException
     */
    public function aiEditorFile(UploadFileRequest $request): JsonResponse
    {
        // 处理文件上传
        $fileInfo = $request->handleUpload();
        $attachment = Attachment::create($fileInfo);

        return response()->json([
            'errorCode' => 0,
            'data' => [
                'href' => $attachment->file_url,
                'fileName' => $attachment->file_name,
            ],
        ]);
    }

    /**
     * TinyMCE 编辑器图片上传
     *
     * @throws ValidationException
     */
    public function tinymce(StoreAttachmentRequest $request): JsonResponse
    {
        // 处理文件上传
        $fileInfo = $request->handleUpload();
        $attachment = Attachment::create($fileInfo);

        return response()->json([
            'file_name' => $attachment->file_name,
            'file_path' => $attachment->file_path,
            'location' => $attachment->file_url,
        ]);
    }
}
