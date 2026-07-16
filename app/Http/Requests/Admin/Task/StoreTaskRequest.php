<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Task;

use App\Enums\TaskType;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 新增任务请求
 */
class StoreTaskRequest extends FormRequest
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
            'activity_bonus' => $this->boolean('activity_bonus'),
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
            'type' => ['required', 'string', 'max:255'],
            'coins' => ['required', 'integer'],

            'condition' => ['nullable', 'array'],
            'condition.played_time' => ['required_if:type,'.TaskType::TYPE_INCENTIVE_VIDEO->value, 'integer'],
            'condition.serial_days' => ['required_if:type,'.TaskType::TYPE_SIGN_IN->value, 'integer'],

            'activity_bonus' => ['nullable', 'boolean'],
            'order' => ['required', 'integer'],
        ];
    }
}
