<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 更新管理菜单
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UpdateAdminMenuRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'max:255'],
            'href' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'integer', 'min:0', 'max:2'],
            'order' => ['required', 'integer', 'min:0'],

            'roles' => ['nullable', 'string'],
        ];
    }
}
