<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Attachment;

use App\Facades\Upload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * 上传视频
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UploadVideoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'mimes:'.settings('upload.allow_video_extension')],
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
        $fileInfo['user_id'] = $this->user_id;

        return $fileInfo;
    }
}
