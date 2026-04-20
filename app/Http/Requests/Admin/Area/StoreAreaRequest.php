<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Area;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
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
            'parent_id' => ['nullable', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'area_code' => ['nullable', 'integer'],
            'city_code' => ['nullable', 'string', 'regex:/^0\d{2,3}$/'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
