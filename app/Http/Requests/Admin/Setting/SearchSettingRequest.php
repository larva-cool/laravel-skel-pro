<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 搜索配置项请求
 *
 * @property-read string $keyword 搜索关键词
 * @property-read string $field 排序字段
 * @property-read string $order 排序方向，asc或desc
 */
class SearchSettingRequest extends FormRequest
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
            'keyword' => ['nullable', 'string'],
            'field' => ['nullable', 'string'],
            'order' => ['nullable', 'in:asc,desc'],
        ];
    }
}
