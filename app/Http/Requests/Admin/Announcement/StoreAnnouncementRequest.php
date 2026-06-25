<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Announcement;

use App\Enum\StatusSwitch;
use App\Enum\UserType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 新增公告请求
 */
class StoreAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * 准备验证数据
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'admin_id' => $this->user('admin')->id,
            'status' => $this->integer('status'),
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
            'admin_id' => ['required', 'integer'],
            'coverage' => ['required', Rule::in(UserType::values())],
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::enum(StatusSwitch::class)],
            'effective_time_type' => ['required', 'integer'],
            'effective_start_time' => [
                'nullable',
                // 当 effective_time_type 为 1 时才验证 required 规则
                'required_if:effective_time_type,1',
                'before:effective_end_time',
            ],
            'effective_end_time' => [
                'nullable',
                // 当 effective_time_type 为 1 时才验证 required 规则
                'required_if:effective_time_type,1',
                'after:effective_start_time',
            ],
        ];
    }
}
