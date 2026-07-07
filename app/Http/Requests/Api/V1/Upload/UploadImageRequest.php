<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Upload;

use App\Facades\Upload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * 上传图片请求
 *
 * @property-read string $field 文件字段名，前端固定传 file
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UploadImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'max:5120'], // 最大 5MB
        ];
    }

    /**
     * 处理文件上传
     *
     * @return array 文件信息（含 file_path、file_name、url）
     *
     * @throws ValidationException
     */
    public function handleUpload(): array
    {
        $fileInfo = Upload::uploadFile($this->file('file'));
        if (! $fileInfo) {
            throw ValidationException::withMessages([
                'file' => '上传失败，请重试',
            ]);
        }

        return $fileInfo;
    }
}
