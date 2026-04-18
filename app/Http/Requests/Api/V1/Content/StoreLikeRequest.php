<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 存储点赞请求
 *
 * @property int $user_id 用户ID
 * @property int $source_id 资源ID
 * @property string $source_type 资源类型
 * @property array $extra 额外数据
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StoreLikeRequest extends FormRequest
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
            'user_id' => $this->user()->id,
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
            'user_id' => ['required', 'integer'],
            'source_id' => ['required', 'integer'],
            'source_type' => ['required', Rule::in(array_keys(get_morph_maps()))],
            'extra' => ['nullable', 'array'],
        ];
    }
}
