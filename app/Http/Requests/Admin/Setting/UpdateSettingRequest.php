<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 更新设置请求
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UpdateSettingRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'param' => ['nullable', 'string', 'max:255'],
            'cast_type' => ['required', 'string', 'max:255'],
            'input_type' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer'],
            'remark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
