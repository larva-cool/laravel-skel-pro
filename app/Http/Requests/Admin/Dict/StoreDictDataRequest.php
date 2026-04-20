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
 * 创建字典数据
 *
 * @property-read int $parent_id 父菜单
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StoreDictDataRequest extends FormRequest
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
            'parent_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:255',
                Rule::unique(Dict::class, 'code')
                    ->whereNotNull('parent_id')
                    ->where('parent_id', $this->parent_id),
            ],
            'status' => ['required', Rule::enum(StatusSwitch::class)],
            'order' => ['nullable', 'integer'],
        ];
    }
}
