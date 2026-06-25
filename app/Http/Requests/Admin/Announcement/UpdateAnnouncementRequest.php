<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Announcement;

use App\Enum\StatusSwitch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 更新公告请求
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UpdateAnnouncementRequest extends FormRequest
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
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::enum(StatusSwitch::class)],
            'effective_time_type' => ['required', 'integer'],
            'effective_start_time' => ['required_if:effective_time_type,1', 'date', 'before:effective_end_time'],
            'effective_end_time' => ['required_if:effective_time_type,1', 'date', 'after:effective_start_time'],
        ];
    }
}
