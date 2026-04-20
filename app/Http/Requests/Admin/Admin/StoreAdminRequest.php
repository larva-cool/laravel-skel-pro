<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Admin;

use App\Models\Admin\Admin;
use App\Rules\PhoneRule;
use App\Rules\UsernameRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * 管理员创建请求
 *
 * @property string $name
 * @property string $username
 * @property string $phone
 * @property string $email
 * @property array $roles
 * @property string $password
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class StoreAdminRequest extends FormRequest
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
        $roles = explode(',', $this->string('roles')->toString());
        $this->merge([
            'roles' => $roles,
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
            'roles' => ['required', 'array'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', new PhoneRule, Rule::unique(Admin::class, 'phone')],
            'username' => ['nullable', 'string', 'max:255', new UsernameRule, Rule::unique(Admin::class, 'username')],
            'email' => ['required', 'string', 'email', Rule::unique(Admin::class, 'email')],
            'password' => ['required', Password::min(8)->uncompromised()],
        ];
    }
}
