<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use App\Rules\PhoneRule;
use App\Rules\UsernameRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * 创建用户请求
 *
 * @property-read string $phone
 * @property-read string $password
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class StoreUserRequest extends FormRequest
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
            'phone' => ['required', new PhoneRule, Rule::unique(User::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', new UsernameRule, Rule::unique(User::class)],
            'email' => ['nullable', 'string', 'email', Rule::unique(User::class)],
            'password' => ['nullable', 'string', Password::min(8)],
        ];
    }
}
