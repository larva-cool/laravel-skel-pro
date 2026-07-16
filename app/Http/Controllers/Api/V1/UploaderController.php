<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Facades\Upload;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Upload\UploadImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * 通用文件上传接口
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UploaderController extends Controller
{
    /**
     * UploaderController Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 上传图片
     *
     * @throws ValidationException
     */
    public function image(UploadImageRequest $request): JsonResponse
    {
        $fileInfo = $request->handleUpload();

        return response()->json([
            'message' => '上传成功',
            'data' => [
                'file_name' => $fileInfo['file_name'],
                'file_path' => $fileInfo['file_path'],
                'url' => Upload::url($fileInfo['file_path']),
            ],
        ]);
    }
}
