<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Report;

use App\Enums\ReportReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 提交举报请求
 *
 * @property-read string $reportable_type 被举报对象类型
 * @property-read int $reportable_id 被举报对象ID
 * @property-read string $reason 举报原因
 * @property-read string|null $content 补充说明
 * @property-read array|null $evidence 证据URL列表
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class StoreReportRequest extends FormRequest
{
    /**
     * 允许被举报的对象类型
     *
     * @var array<int, string>
     */
    protected array $allowedTypes = ['comment', 'user'];

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
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'reportable_type' => ['required', 'string', Rule::in($this->allowedTypes)],
            'reportable_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', Rule::in(ReportReason::values())],
            'content' => ['nullable', 'string', 'max:500'],
            'evidence' => ['nullable', 'array', 'max:9'],
            'evidence.*' => ['required', 'string', 'url', 'max:500'],
            'ip_address' => ['required', 'ip'],
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
            'reportable_type.required' => '被举报对象类型不能为空',
            'reportable_type.in' => '被举报对象类型不合法',
            'reportable_id.required' => '被举报对象ID不能为空',
            'reason.required' => '举报原因不能为空',
            'reason.in' => '举报原因不合法',
            'content.max' => '补充说明不能超过500个字符',
            'evidence.max' => '证据不能超过9个',
            'evidence.*.url' => '证据必须是合法的URL',
        ];
    }
}
