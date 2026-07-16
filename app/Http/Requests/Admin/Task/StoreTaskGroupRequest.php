<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Task;

use App\Enums\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 任务分组创建请求
 */
class StoreTaskGroupRequest extends FormRequest
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
        $this->merge([
            'visibility' => $this->boolean('visibility'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(TaskType::values())],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
            'visibility' => ['required', 'boolean'],
            'order' => ['required', 'integer'],
        ];
    }
}
