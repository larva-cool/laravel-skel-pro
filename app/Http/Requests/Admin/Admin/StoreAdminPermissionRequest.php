<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 保存权限
 * @property array $menus
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StoreAdminPermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 准备验证数据
     */
    protected function prepareForValidation(): void
    {
        $httpMethods = explode(',', $this->string('http_method')->toString());
        $httpPaths = explode(',', $this->string('http_path')->toString());
        $menus = explode(',', $this->string('menus')->toString());
        $this->merge([
            'http_method' => $httpMethods,
            'http_path' => $httpPaths,
            'menus' => $menus,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'http_method' => ['required', 'array'],
            'http_path' => ['required', 'array'],
            'order' => ['required', 'integer', 'min:0'],
            'menus' => ['nullable', 'array'],
        ];
    }
}
