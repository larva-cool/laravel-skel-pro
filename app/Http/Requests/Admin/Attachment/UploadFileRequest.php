<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Attachment;

use App\Facades\Upload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * 上传文件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UploadFileRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:'.settings('upload.allow_extension')],
        ];
    }

    /**
     * 处理附件上传
     */
    public function handleUpload(): array
    {
        // 上传文件
        $fileInfo = Upload::uploadFile($this->file);
        if (! $fileInfo) {
            throw ValidationException::withMessages([
                'file' => trans('system.upload_failed'),
            ]);
        }

        return $fileInfo;
    }
}
