<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 评论请求
 * 用于验证评论创建的请求数据
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 只有登录用户可以评论
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
            'user_id' => ['required', 'numeric', Rule::exists(User::class, 'id')],
            'source_id' => ['required', 'numeric'],
            'source_type' => ['required', 'string', 'max:255', Rule::in(array_keys(get_morph_maps()))],
            'content' => ['required', 'string', 'min:2', 'max:1000'],
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
            'user_id.required' => '用户ID不能为空',
            'user_id.numeric' => '用户ID必须是数字',
            'user_id.exists' => '用户不存在',
            'source_id.required' => '来源ID不能为空',
            'source_id.numeric' => '来源ID必须是数字',
            'source_type.required' => '来源类型不能为空',
            'source_type.string' => '来源类型必须是字符串',
            'source_type.max' => '来源类型长度不能超过255个字符',
            'source_type.in' => '来源类型必须是article、comment或post中的一个',
            'content.required' => '评论内容不能为空',
            'content.string' => '评论内容必须是字符串',
            'content.min' => '评论内容不能少于2个字符',
            'content.max' => '评论内容不能超过1000个字符',
        ];
    }
}
