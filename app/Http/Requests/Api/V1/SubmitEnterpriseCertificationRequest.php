<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Rules\PhoneRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 企业实名认证请求验证
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class SubmitEnterpriseCertificationRequest extends FormRequest
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
            'enterprise_name' => 'required|string|max:100',
            'license_no' => 'required|string|max:50',
            'license' => 'required|string|max:500',
            'contact_person' => 'required|string|max:50',
            'contact_phone' => ['required', new PhoneRule],
            'contact_email' => 'required|email|max:100',
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
            'enterprise_name.required' => '企业名称不能为空',
            'enterprise_name.max' => '企业名称不能超过100个字符',
            'license_no.required' => '营业执照号码不能为空',
            'license_no.max' => '营业执照号码不能超过50个字符',
            'license.required' => '营业执照照片不能为空',
            'license.max' => '营业执照照片路径过长',
            'contact_person.required' => '联系人姓名不能为空',
            'contact_person.max' => '联系人姓名不能超过50个字符',
            'contact_phone.required' => '联系人手机号不能为空',
            'contact_phone.phone' => '联系人手机号格式不正确',
            'contact_email.required' => '联系邮箱不能为空',
            'contact_email.email' => '联系邮箱格式不正确',
            'contact_email.max' => '联系邮箱不能超过100个字符',
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
