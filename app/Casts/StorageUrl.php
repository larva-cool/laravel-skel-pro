<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Casts;

use App\Services\UploadService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * 存储转换
 *
 * 用于将存储路径转换为完整URL
 * 并在写入时去除URL前缀
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StorageUrl implements CastsAttributes
{
    /**
     * 文件服务实例
     */
    protected UploadService $fileService;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->fileService = UploadService::getInstance();
    }

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return $value;
        }

        return $this->fileService->url($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $this->fileService->relativePath($value);
    }
}
