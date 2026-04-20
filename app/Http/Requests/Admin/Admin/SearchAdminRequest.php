<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 用户搜索
 *
 * @property string $keyword 搜索关键词
 * @property array $last_login_at 最后登录时间
 * @property string $field 排序字段
 * @property string $order 排序方式
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class SearchAdminRequest extends FormRequest
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
            'keyword' => 'nullable|string|max:255',
            'file_ext' => 'nullable|string|max:255',
            'last_login_at' => 'nullable|array',
            'last_login_at.*' => 'nullable|date',
            'field' => 'nullable|string|max:255',
            'order' => 'nullable|string|max:255',
        ];
    }
}
