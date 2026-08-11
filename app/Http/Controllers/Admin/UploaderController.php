<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Uploader\UploadTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * 上传控制器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UploaderController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * 通用图片上传
     *
     * @return array
     */
    public function image(Request $request)
    {
        return [];
    }

    /**
     * 读取远程上传 Token
     */
    public function uploadToken(UploadTokenRequest $request): JsonResponse
    {
        $path = 'uploads/'.md5(uniqid().microtime()).$request->filename;

        $result = Storage::temporaryUploadUrl($path, Carbon::now()->addMinutes(10));

        return response()->json([
            'url' => $result['url'],
            'headers' => $result['headers'],
            'path' => $path,
        ]);
    }
}
