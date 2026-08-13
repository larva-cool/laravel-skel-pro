<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Requests\Admin\Admin;

use App\Http\Requests\Admin\Uploader\ImageUploadRequest;

class AdminUpdateAvatarRequest extends ImageUploadRequest
{
    /**
     * 获取文件存储目录
     */
    public function getDirectory(): string
    {
        return 'avatars/'.date('Y/m/d');
    }
}
