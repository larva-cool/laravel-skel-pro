<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Feedback;

use App\Enums\FeedbackType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 提交反馈请求
 *
 * @property-read string $type 反馈类型
 * @property-read string|null $title 标题
 * @property-read string $content 反馈内容
 * @property-read string|null $contact 联系方式
 * @property-read array|null $attachments 附件URL列表
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class StoreFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 准备验证数据
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()?->id,
            'ip_address' => $this->ip(),
            'user_agent' => (string) $this->userAgent(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'type' => ['required', 'string', Rule::in(FeedbackType::values())],
            'title' => ['nullable', 'string', 'max:120'],
            'content' => ['required', 'string', 'min:2', 'max:2000'],
            'contact' => ['nullable', 'string', 'max:100'],
            'attachments' => ['nullable', 'array', 'max:9'],
            'attachments.*' => ['required', 'string', 'url', 'max:500'],
            'ip_address' => ['required', 'ip'],
            'user_agent' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => '反馈类型不能为空',
            'type.in' => '反馈类型不合法',
            'content.required' => '反馈内容不能为空',
            'content.min' => '反馈内容不能少于2个字符',
            'content.max' => '反馈内容不能超过2000个字符',
            'title.max' => '标题不能超过120个字符',
            'contact.max' => '联系方式不能超过100个字符',
            'attachments.array' => '附件必须是数组',
            'attachments.max' => '附件不能超过9个',
            'attachments.*.url' => '附件必须是合法的URL',
        ];
    }
}
