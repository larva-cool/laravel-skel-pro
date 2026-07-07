<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Rules\IdCardRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 个人实名认证请求验证
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class SubmitPersonalCertificationRequest extends FormRequest
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
            'real_name' => 'required|string|max:50',
            'id_card_no' => ['required', new IdCardRule],
            'id_card_front' => 'required|string|max:500',
            'id_card_back' => 'required|string|max:500',
            'id_card_in_hand' => 'required|string|max:500',
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
            'real_name.required' => '真实姓名不能为空',
            'real_name.max' => '真实姓名不能超过50个字符',
            'id_card_no.required' => '身份证号码不能为空',
            'id_card_no.id_card' => '身份证号码格式不正确',
            'id_card_front.required' => '身份证正面照片不能为空',
            'id_card_front.max' => '身份证正面照片路径过长',
            'id_card_back.required' => '身份证背面照片不能为空',
            'id_card_back.max' => '身份证背面照片路径过长',
            'id_card_in_hand.required' => '手持身份证照片不能为空',
            'id_card_in_hand.max' => '手持身份证照片路径过长',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => '验证失败',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
