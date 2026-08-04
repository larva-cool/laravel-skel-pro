<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */
namespace App\Http\Requests\Admin\Uploader;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 远程上传 Token
 *
 * @property-read string $filename 文件名
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UploadTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filename' => ['required', 'string'],
        ];
    }
}
