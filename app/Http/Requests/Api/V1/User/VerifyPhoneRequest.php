<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Models\User;
use App\Rules\PhoneRule;
use App\Rules\SmsCaptchaRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 验证手机号码请求
 *
 * @property-read int $phone 手机号码
 * @property-read string $verify_code 验证码
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class VerifyPhoneRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', new PhoneRule, Rule::exists(User::class, 'phone')],
            'verify_code' => ['required', 'min:4', 'max:6', new SmsCaptchaRule('phone', $this->ip())],
        ];
    }
}
