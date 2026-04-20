<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Dict;

use App\Enum\StatusSwitch;
use App\Models\System\Dict;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 创建字典
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StoreDictRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique(Dict::class, 'code')->whereNull('parent_id')],
            'status' => ['required', Rule::enum(StatusSwitch::class)],
            'order' => ['nullable', 'integer'],
        ];
    }
}
